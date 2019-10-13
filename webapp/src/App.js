import React from "react";
import axios from "axios";
import config from "./config";
import "./sass/styles.scss";

class App extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      courses: [],
      selectedCourse: false,
      hasEmptyParticipantFields: true,
      error: false,
      formData: this.getCleanFormData(),
      message: ""
    };
    this.fetchCourses();
    this.onSelectCourseChange = this.onSelectCourseChange.bind(this);
    this.onSelectDateChange = this.onSelectDateChange.bind(this);
    this.onTextFieldChange = this.onTextFieldChange.bind(this);
    this.addParticipant = this.addParticipant.bind(this);
    this.onFormSubmit = this.onFormSubmit.bind(this);
  }

  getCleanFormData() {
    return Object.assign(
      {},
      {
        course_id: false,
        date_id: false,
        company_name: "",
        company_phone: "",
        company_email: "",
        participants: [this.getCleanParticipant()]
      }
    );
  }

  getCleanParticipant() {
    return Object.assign(
      {},
      {
        name: "",
        phone: "",
        email: ""
      }
    );
  }

  fetchCourses() {
    axios
      .get(config.apiBase + "courses")
      .then(response => {
        this.setState(
          {
            courses: response.data
          },
          () => {
            this.selectCourse(response.data[0].id);
          }
        );
      })
      .catch(error => {
        console.log(error);
      })
      .finally(() => {});
  }

  onTextFieldChange(e) {
    this.setState({
      formData: this.mergeFormData({
        [e.target.id]: e.target.value
      }),
      error: false
    });
  }

  onSelectCourseChange(e) {
    const selectedId = parseInt(e.target.value);
    this.selectCourse(selectedId);
  }

  selectCourse(selectedId) {
    const selectedCourse = this.state.courses.filter(course => {
      return course.id === selectedId;
    })[0];
    if (!selectedCourse) {
      return;
    }
    this.setState({
      selectedCourse,
      formData: this.mergeFormData({
        course_id: parseInt(selectedCourse.id),
        date_id: parseInt(selectedCourse.dates[0].id)
      }),
      error: false
    });
  }

  onSelectDateChange(e) {
    this.setState({
      formData: this.mergeFormData({
        date_id: parseInt(e.target.value)
      }),
      error: false
    });
  }

  mergeFormData(input) {
    return Object.assign({}, this.state.formData, input);
  }

  renderCourseSelector() {
    if (!this.state.selectedCourse) {
      return;
    }
    return (
      <select
        className="form-control"
        id="course_id"
        onChange={this.onSelectCourseChange}
        value={this.state.formData.course_id}
      >
        {this.state.courses.map(course => {
          return (
            <option key={course.id} value={course.id}>
              {course.name}
            </option>
          );
        })}
      </select>
    );
  }

  renderDateSelector() {
    if (!this.state.selectedCourse) {
      return null;
    }
    return (
      <select
        className="form-control"
        id="date_id"
        onChange={this.onSelectDateChange}
        value={this.state.formData.date_id}
      >
        {this.state.selectedCourse.dates.map(date => {
          return (
            <option key={date.id} value={date.id}>
              {date.date}
            </option>
          );
        })}
      </select>
    );
  }

  renderParticipant(part, i) {
    return (
      <div key={i}>
        <h2>Participant #{i + 1}</h2>
        <div className="row">
          <div className="col-12 form-group">
            <label htmlFor="name">Name *</label>
            <input
              type="text"
              className={"form-control" + this.invalidPartClass("name", i)}
              id="name"
              onChange={e => this.updateParticipant(e, i)}
            />
          </div>
          <div className="col-12 col-md-5 form-group">
            <label htmlFor="phone">Phone *</label>
            <input
              type="text"
              className={"form-control" + this.invalidPartClass("phone", i)}
              id="phone"
              onChange={e => this.updateParticipant(e, i)}
            />
          </div>
          <div className="col-12 col-md-7 form-group">
            <label htmlFor="email">Email *</label>
            <input
              type="text"
              className={"form-control" + this.invalidPartClass("email", i)}
              id="email"
              onChange={e => this.updateParticipant(e, i)}
            />
          </div>
        </div>
      </div>
    );
  }

  addParticipant(e) {
    e.preventDefault();
    const formData = JSON.parse(JSON.stringify(this.state.formData));
    formData.participants.push(this.getCleanParticipant());
    this.setState({
      formData,
      hasEmptyParticipantFields: true,
      error: false
    });
  }

  updateParticipant(e, i) {
    const formData = JSON.parse(JSON.stringify(this.state.formData));
    formData.participants[i][e.target.id] = e.target.value;

    const hasEmptyParticipantFields =
      formData.participants.filter(part => {
        return part.name === "" || part.phone === "" || part.email === "";
      }).length !== 0;

    this.setState({
      formData,
      hasEmptyParticipantFields,
      error: false
    });
  }

  renderParticipants() {
    const parts = this.state.formData.participants;
    const disabled = this.state.hasEmptyParticipantFields;
    return (
      <div className="participants">
        {parts.map((part, i) => {
          return this.renderParticipant(part, i);
        })}
        <button
          className="btn btn-primary"
          onClick={this.addParticipant}
          disabled={disabled}
        >
          Add Participant
        </button>
      </div>
    );
  }

  onFormSubmit(e) {
    e.preventDefault();
    axios
      .post(config.apiBase + "applications", this.state.formData)
      .then(response => {
        console.log(response.data);
        this.setState({
          formData: this.getCleanFormData(),
          message: "Thank you!"
        });
      })
      .catch((error, res) => {
        const response = error.response.data;
        if (response.details && response.details.field) {
          this.setState({
            error: response.details
          });
        }
      })
      .finally(() => {});
  }

  invalidClass(key) {
    let res = "";
    if (this.state.error && this.state.error.field === key) {
      return " is-invalid";
    }
    return res;
  }

  invalidPartClass(key, i) {
    let res = "";

    const errorField = this.state.error && this.state.error.field;

    if (errorField && errorField["participant_" + key] === i) {
      return " is-invalid";
    }
    return res;
  }

  render() {
    return (
      <div className="App">
        <form onSubmit={this.onFormSubmit}>
          <div className="section">
            <div className="container">
              <h1>Course</h1>
              <div className="row">
                <div className="col-12 col-md-6 form-group">
                  <label htmlFor="course_id">Name *</label>
                  {this.renderCourseSelector()}
                </div>
                <div className="col-12 col-md-6 form-group">
                  <label htmlFor="usr">Date *</label>
                  {this.renderDateSelector()}
                </div>
              </div>
            </div>
          </div>
          <div className="section bg-brown">
            <div className="container">
              <h1>Company</h1>
              <div className="row">
                <div className="col-12 form-group">
                  <label htmlFor="company_name">Name *</label>
                  <input
                    type="text"
                    className={
                      "form-control" + this.invalidClass("company_name")
                    }
                    id="company_name"
                    onChange={this.onTextFieldChange}
                  />
                </div>
                <div className="col-12 col-md-5 form-group">
                  <label htmlFor="company_phone">Phone *</label>
                  <input
                    type="text"
                    className={
                      "form-control" + this.invalidClass("company_phone")
                    }
                    id="company_phone"
                    onChange={this.onTextFieldChange}
                  />
                </div>
                <div className="col-12 col-md-7 form-group">
                  <label htmlFor="company_email">Email *</label>
                  <input
                    type="text"
                    className={
                      "form-control" + this.invalidClass("company_email")
                    }
                    id="company_email"
                    onChange={this.onTextFieldChange}
                  />
                </div>
              </div>
            </div>
          </div>
          <div className="section bg-beige">
            <div className="container">
              <h1>Participants</h1>
              {this.renderParticipants()}
            </div>
          </div>
          <div className="section">
            <div className="container">
              <button className="btn btn-primary btn-block">Submit</button>
            </div>
          </div>
          <div className="section">
            <div className="container">
              <p>{this.state.message}</p>
            </div>
          </div>
        </form>
      </div>
    );
  }
}

export default App;
