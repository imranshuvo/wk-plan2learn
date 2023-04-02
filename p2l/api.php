<?php 
//ini_set('display_errors', 1);

$apiUrl = 'https://nlphuset.plan2learn.dk/WebServices/Catalog.asmx?wsdl';
$headers = [
	'CustomerGuid' => '2e0dd016-7a4b-4c62-8c4b-d924669c4140'
];


function getCourse($course_id){
    
    global $headers, $apiUrl;
    
    $client = new SoapClient($apiUrl);
    
    $headers['CourseId'] = $course_id;
    $courseData = $client->GetCourse($headers);
    $courseStatus = $courseData->GetCourseResult->ResponseOk;
    $courseMessage = $courseData->GetCourseResult->ResponseMessage;
    
    //This is individual Course data
    $wk_course = $courseData->GetCourseResult->Courses->Course;
    
    
    return $wk_course;
    
    // echo '<pre>';
    // print_r($wk_course);
    // echo '</pre>';
}


function getAllCourse(){
    
    global $apiUrl, $headers;
    
    try {
        $client = new SoapClient($apiUrl);
        $response = $client->GetAllCourses($headers);
        
        
        //Response data
        $data = $response->GetAllCoursesResult;
        $statusCode = $data->ResponseOk;
        $statusMessage = $data->ResponseMessage;
        $courses = $data->Courses->Course;
        
        
        if(count($courses) > 0){
            return $courses;
        }else {
            return false;
        }
        
    } catch ( Exception $e){
        //return $e->getMessage();
        return false;
    }

}

//getCourse(89687);


add_action('wp_ajax_wk_get_plan2learn_courses','wk_get_plan2learn_courses');
add_action('wp_ajax_nopriv_wk_get_plan2learn_courses','wk_get_plan2learn_courses');

function wk_get_plan2learn_courses(){
    $courses = getAllCourse();
    $all_courses = array();
    
    if($courses){
        foreach($courses as $s_course) {
            if(84380 == $s_course->CourseId){
                continue;
            }
            $course = getCourse($s_course->CourseId);
            
            
            //Here we'll have to perform the database insert operation
            //course_id 
            $course_id = $course->CourseId;
            $title = !empty($course->CourseName) ? $course->CourseName : '';
            $description = !empty($course->DescriptionLong) ? $course->DescriptionLong: '' ;
            $excerpt = !empty($course->DescriptionShort) ? $course->DescriptionShort : '' ;
            $category = !empty($course->Category) ? $course->Category : 'Coach' ;
            $subcategory = !empty($course->SubCategory) ? $course->SubCategory : 'Coaching' ;
            $tags = maybe_serialize($course->Tags); //object of arrays
            
            //Teams; goes to post meta table
            $teams = $course->Teams->Team; //Object of object and arrays
            $team_id = property_exists($teams, 'TeamId') ? $teams->TeamId : '' ;
            $team_number = property_exists($teams, 'TeamNumber') ? $teams->TeamNumber: '';
            $seats = property_exists($teams, 'Seats') ? $teams->Seats : '';
            $registrationDeadline = property_exists($teams, 'RegistrationDeadline') ? $teams->RegistrationDeadline : '';
            $participantsSignedUP = property_exists($teams, 'ParticipantsSignedUp') ? $teams->ParticipantsSignedUp : '';
            $participantswaitingList = property_exists($teams, 'ParticipantsWaitingList') ? $teams->ParticipantsWaitingList : '';
            $startDateTime = property_exists($teams, 'StartDateTime') ? $teams->StartDateTime : '';
            $signupUrl = property_exists($teams, 'SignupURL') ? $teams->SignupURL : '';
            
            $price_html = !empty($course->Price) ? $course->Price : '';
            $price_description = !empty($course->PriceDescription) ? $course->PriceDescription : '';
            
            //Price array
            $prices = maybe_serialize($course->Prices->Price);
            
            
            
            
            //Teamperiods; serialize and store this in the post meta table
            $teamperiods = maybe_serialize($teams->TeamPeriods->TeamPeriod); //Object of arrays
            
            /** 
             * Now the database insert operation
             **/
            //First the category
            $parent_cat_exist = term_exists($category, 'p2l_categories');
            
            if( $parent_cat_exist == null ){
                //category doesn't exist
                $returnCategory = wp_insert_term(
                    $category,
                    'p2l_categories'
                );
                
                $category_id = $returnCategory['term_id'];

                //so, we have added the category; now add the subcategory 
                $subcat_exists = term_exists($subcategory,'p2l_categories');
                
                if($subcat_exists == null ){
                    //sub cat doesn't exist
                    $returnSubCategory = wp_insert_term(
                        $subcategory,
                        'p2l_categories',
                         array(
                             'parent' => $category_id,
                        )
                    );
                    $subcategory_id = $returnSubCategory['term_id'];
                }else{
                    //sub cat exists
                    $subcategory_id = $subcat_exists['term_id'];
                }
                    
            }else{
                //Category exists; so just add the subcategory but first check if subcate also exists
                $category_id = $parent_cat_exist['term_id'];
                
                $subcat_exists = term_exists($subcategory,'p2l_categories');
                
                if($subcat_exists == null ){
                    //sub cat doesn't exist
                    $returnSubCategory = wp_insert_term(
                        $subcategory,
                        'p2l_categories',
                         array(
                             'parent' => $category_id,
                        )
                    );
                    $subcategory_id = $returnSubCategory['term_id'];
                }else{
                    //subcat exists
                    $subcategory_id = $subcat_exists['term_id'];
                }
                
            }
            
            
            //We now have the category and subcategory id
            //Now the post
            //will have title, description, excerpt, category and subcategory; everything else goes to the meta data
            //structure the post array first
            $post_data = array(
                'post_title' => $title,
                'post_excerpt' => $excerpt,
                'post_content' => $description,
                'post_type' => 'plan2learn',
                'post_status' => 'publish',
                //'post_category' => array($category_id, $subcategory_id),
                'meta_input' => array(
                    'tags' => $tags,
                    'team_id' => $team_id,
                    'team_number' => $team_number,
                    'seats' => $seats,
                    'registration_deadline' => $registrationDeadline,
                    'participants_signed_up' => $participantsSignedUP,
                    'participants_waiting_list' => $participantswaitingList,
                    'start_date_time' => $startDateTime,
                    'signup_url' => $signupUrl,
                    'team_periods' => $teamperiods,
                    'price_html' => $price_html,
                    'price_description' => $price_description,
                    'prices' => $prices,
                ),
            );
            
            if(post_exists($title, '', '', 'plan2learn') != 0){
                //post already exists 
                $post_data['ID'] = post_exists($title, '', '', 'plan2learn');
            }
            
            $post_obj = wp_insert_post($post_data);
            
            if(!is_wp_error($post_obj)){
                //Post created, now add the category 
                wp_set_post_terms($post_obj, array($category_id, $subcategory_id),'p2l_categories', true );
            }else{
                //Something went wrong;
                $result = array(
                    'error' => 'Something went wrong. Please debug'   
                );
                break;
            }
            
            //This is temporary to just get the data
            //array_push($all_courses, $s_course);
        }
        
        if(!empty($result['error'])){
            //there's error 
            $result['error'] = 'Something definitely went wrong!';
        }else{
            //there's no error 
            $result['message'] = 'That worked like charm';
        }
        
    }else{
        $result = array(
          'error' => 'Something went wrong. Please debug'   
        );
    }
    
    
    echo json_encode($result);
    wp_die();
}