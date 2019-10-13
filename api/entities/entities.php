<?php
class Course extends ORM\Entity {
    protected static $relations = [
        'dates' => [CourseDate::class, 'course'],
        'applications' => [Application::class, 'course']
    ];

    public static function format( $course ) {
        $dates = $course->fetch('dates')->all();
        return [
            'id' => $course->id,
            'name' => $course->course_name,
            'dates' => array_map( ['CourseDate', 'format'], $dates ),
        ];
    }
}



class CourseDate extends ORM\Entity {
    protected static $relations = [
        'course' => [Course::class, ['course_id' => 'id']],
        'applications' => [Application::class, 'date']
    ];

    public static function format( $date ) {
        return [
            'id' => $date->id,
            'date' => $date->course_date,
        ];
    }
}

class Application extends ORM\Entity {
    protected static $relations = [
        'participants' => [Participant::class, 'application'],
        'date' => [CourseDate::class, ['date_id' => 'id']],
        'course' => [Course::class, ['course_id' => 'id']],
    ];

    public static function format( $appl ) {
        $participants = $appl->fetch('participants')->all();
        $course = $appl->fetch('course');
        $date = $appl->fetch('date');
        return [
            'id' => $appl->id,
            'course' => Course::format( $course ),
            'date' => CourseDate::format( $date ),
            'participants' => array_map( ['Participant', 'format'], $participants ),
            'company' => [
                'name' => $appl->company_name,
                'phone' => $appl->company_phone,
                'email' => $appl->company_email,
            ]
        ];
    }

    public static function add_new( $data ) {
        $valid_input = static::validate_input( $data );
        $new_appl = new static();
        $new_appl->fill($valid_input);
        // return static::format($new_appl);
        $new_appl->save();
        if( !empty( $data['participants']) ) {
            foreach( $data['participants'] as $i => $input_part) {
                $email = static::validate_email($input_part['email'], ['participant_email' => $i]);
                $phone = static::validate_phone($input_part['phone'], ['participant_phone' => $i]);

                $part = new Participant([
                    'participant_name' => $input_part['name'] ?? '',
                    'participant_email' => $email,
                    'participant_phone' => $phone,
                ]);
                $part->setRelated('application', $new_appl);
                $part->save();
            }
        }
        return static::format($new_appl);
    }

    public static function validate_input( $data ) {
        $appl_input = [
            'course_id',
            'date_id',
            'company_name',
            'company_email',
            'company_phone',
        ];
        $res = [];
        foreach( $appl_input as $key ) {
            if( !isset($data[ $key ]) ) {
                $e = new ApiException( "Missing field" );
                $e->setOptions([
                    'field' => $key
                ]);
                throw $e;
            }
            $valid = static::validate($key, $data[ $key ]);
            if( $valid !== true ) {
                $e = new ApiException( $valid->getMessage() );
                $e->setOptions([
                    'field' => $key,
                    'value' => $data[ $key ]
                ]);
                throw $e;
            }
            $res[ $key ] = $data[ $key ];
        }
        $res[ 'company_email' ] = static::validate_email($res[ 'company_email' ], 'company_email');
        $res[ 'company_phone' ] = static::validate_phone($res[ 'company_phone' ], 'company_phone');

        return $res;
    }

    /**
     * validate email
     * 
     * @throws ApiException on not valid email
     * @return string input email
     */
    public static function validate_email( $string, $field ) {
        if( !filter_var($string, FILTER_VALIDATE_EMAIL) ) {
            $e = new ApiException( "Not a valid email" );
            $e->setOptions([
                'field' => $field,
                'value' => $string
            ]);
            throw $e;
        }
        return $string;
    }

    /**
     * validate phone
     * 
     * @throws ApiException on not valid phone
     * 
     * @return string input phone
     */
    public static function validate_phone( $string, $field ) {
        // Allow +, - and . in phone number
        $filtered_phone_number = filter_var($string, FILTER_SANITIZE_NUMBER_INT);
        $valid = $filtered_phone_number === str_replace(" ", "", $string);

        // Remove "-" from number
        $phone_to_check = str_replace("-", "", $filtered_phone_number);

        // Check the lenght of number
        if ( !$valid || !$filtered_phone_number || strlen($phone_to_check) < 8 || strlen($phone_to_check) > 14) {
            $e = new ApiException( "Not a valid phone number" );
            $e->setOptions([
                'field' => $field,
                'value' => $string
            ]);
            throw $e;
        }
        return $string;
    }
}



class Participant extends ORM\Entity {
    protected static $relations = [
        'application' => [Application::class, ['application_id' => 'id']]
    ];

    public static function format( $participant ) {
        return [
            'id' => $participant->id,
            'name' => $participant->participant_name,
            'phone' => $participant->participant_phone,
            'email' => $participant->participant_email,
        ];
    }
}
