<?php 

function groupby($data) {
    $result = array();
    
    foreach($data as $val){
       if(property_exists($val, 'Subject')){
           $result[$val->Subject][] = $val;
       }
    }
    
    return $result;
}




function html_for_post($post){

    //Get the team periods and other data 
    $teamperiods = maybe_unserialize(get_post_meta($post->ID,'team_periods', true ));
    $modules = groupby($teamperiods);
    
    //starting year
    $start_year = date('Y', strtotime($teamperiods[0]->StartTime));
    
    //Prices 
    $price = '';
    $vatIncluded = '';
    $prices = maybe_unserialize(get_post_meta($post->ID, 'prices', true ));
    if(count($prices) == 1) {
        //There's just one price
        $price = $prices[0]->Price;
        $vatIncluded = $prices[0]->PriceIncludingVat;
        
    }elseif(count($prices == 2)){
        //There's a range 
        if($prices[0]->Price > $prices[1]->Price ){
            $price = $prices[0]->Price;
            $vatIncluded = $prices[0]->PriceIncludingVat;
        }else{
            $price = $prices[1]->Price;
            $vatIncluded = $prices[1]->PriceIncludingVat;
        }
    }else{
        //there's more
        $price = $prices[count($prices) - 1]->Price;
        $vatIncluded = $prices[count($prices) - 1]->PriceIncludingVat;
    }
    
    if($price > $vatIncluded){
        //Vat included
        $vat_text = 'inkl. moms';
    }else{
        //vat excluded
        $vat_text = 'ekskl. moms';
    }
    ?>
    <div id="" class="p2lhold-container">
        <style>
            .tooltip {
              position: relative;
              display: inline-block;
            }
            
            .tooltip .tooltiptext {
              visibility: hidden;
              width: 120px;
              background-color: black;
              color: #fff;
              text-align: center;
              border-radius: 6px;
              padding: 5px 0;
              
              /* Position the tooltip */
              position: absolute;
              z-index: 1;
              bottom: 100%;
              left: 50%;
              margin-left: -60px;
            }
            
            .tooltip:hover .tooltiptext {
              visibility: visible;
            }
            .bundles {
                overflow: hidden;
            }
            em.icon-organizers {
                color: #01a7bf;
                font-weight: bold;
            }
            
            @media all and (min-width: 768px){
                .p2l-content-area .p2lhold-container {
                    width: 50%;
                    float: left;
                }
               .p2l-content-area .p2lhold-container .scbundle {
                   width: 100%;
                   float: none;
               }
               .p2l-content-area .p2lhold-container .bundles {
                   padding-left: 10px;
                   padding-right: 10px;
               }
               .p2l-content-area {
                    display: flex;
                    align-items: center;
                    justify-content: center;
                }
                .p2l-content-area.doubleplus {
                    flex-wrap: wrap;
                    justify-content: left;
                }
            }   
            
        </style>
        <div class="bundles">
            <div class="scbundle">
                <div class="scbundle_container">
            		<div class="scbundle_header">
            			<h3><?php echo $post->post_title;?>, <?php echo $start_year; ?></h3> 
            			<span class="price pid262852" id="262852">
            			    <span class="woocommerce-Price-amount amount">
            			        <bdi><?php echo number_format($price, 0, ',','.'); ?>&nbsp;&nbsp;
            			            <span class="woocommerce-Price-currencySymbol" style="padding-left: 0px;"><?php echo $prices[0]->CurrencyCode; ?></span>
            			        </bdi>
            			     </span> <?php echo $vat_text; ?>
            			  </span>
            		</div>
            		<div class="product-bundle-button">			
            		    <a rel="nofollow" href="<?php echo esc_url(get_post_meta($post->ID,'signup_url', true )); ?>" class="button product_type_bundle add_to_cart_button ajax_add_to_cart %CLASS%">Tilmeld</a>	
            		</div>
            		<div style="clear: both;"></div>
            	</div>
            	
            	<div class="scbundle_content">
            	    <?php foreach($modules as $module => $value):  ?>
            	    
            	    <?php
            	    
            	   // echo '<pre>';
            	   // print_r($value[0]);
            	   // echo '</pre>';
            	    ?>
            	    <div class="scbundle_body">
            	        <div class="bundle-product-title">
            	            <strong><?php echo $post->post_title;?> <?php echo $start_year; ?>, <?php echo $module; ?> - Datoer: 
            	            <?php
            	            $dates_arr = array();
            	            $dates_text = '';
            	            foreach($value as $date): 
            	                array_push($dates_arr, date('d/m/Y',strtotime($date->StartTime)));
            	            endforeach; 
            	            
            	            echo implode(", ", $dates_arr); 
            	            
            	            ?>
            	            </strong>
            	            <br>
            	            <em class="icon-hours"><img src="/wp-content/uploads/2018/03/clock.png"><?php echo date('H:i',strtotime($value[0]->StartTime)); ?> - <?php echo date('H:i',strtotime($value[0]->EndTime)); ?></em>
            	            <em class="icon-location tooltip"><img src="/wp-content/uploads/2018/03/pin.png"><a href="https://<?php echo $value[0]->LocationHomepage; ?>"><?php echo $value[0]->Room; ?></a>
            	                <span class="tooltiptext"><?php echo $value[0]->Location.', '.$value[0]->LocationStreetNumber.', '.$value[0]->LocationZipcodeCity; ?></span>
            	            </em>
            	            <?php 
            	            $ins = array();
            	            foreach($value[0]->Instructors->Instructor as $instructor){
            	                array_push($ins, $instructor->Name);
            	            }
            	            
            	            if(count($ins) > 0){
            	                echo '<em class="icon-organizers"><img src="/wp-content/uploads/2018/03/teachers.png">';
            	                 echo implode(", ", $ins);
            	                 echo '</em>';
            	            }
            	           
            	            
            	            ?>
            	        </div>
            	    </div>
            	    <?php endforeach; ?>
            	</div>
            </div>
        </div>
    </div>
    
    <?php 
}



//shortcode
add_shortcode('kurser','kurser_shortcode_func');

function kurser_shortcode_func($atts) {
    $data = shortcode_atts(array(
        $data['ids'] => ''
        ), $atts);

    ob_start();  
    if($atts['ids'] != ''){
        
        if(count($atts['ids']) > 2){
            $class = "doubleplus";
        }else{
            $class= "";
        }
        //There's individuals
        $ids = explode(',', $atts['ids']);
        
        echo '<div class="p2l-content-area'.$class.'">';
        

        foreach($ids as $id){
            $post = get_post($id);
            if($post){
                html_for_post($post);
            }
        }
        echo '</div>';
    }else{
        //empty, so show all
        $posts = get_posts(array(
            'numberposts' => -1,
            'post_type' => 'plan2learn'
        ));
        
        if(count($posts) > 0){
            if(count($posts) > 2){
                $class = "doubleplus";
            }else{
                $class= "";
            }
            
            echo '<div class="p2l-content-area '.$class.'">';
          
            foreach($posts as $post){
                html_for_post($post);
            }
            echo '</div>';
        }
        
    }
    
    return ob_get_clean();
}


add_filter( 'manage_posts_columns', 'revealid_add_id_column', 5 );
add_action( 'manage_posts_custom_column', 'revealid_id_column_content', 5, 2 );


function revealid_add_id_column( $columns ) {
   $columns['revealid_id'] = 'ID';
   return $columns;
}

function revealid_id_column_content( $column, $id ) {
  if( 'revealid_id' == $column ) {
    echo $id;
  }
}

