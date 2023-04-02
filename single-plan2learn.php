<?php 
require_once('p2l/helper.php');

global $post;

get_header();

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
<div class="site-content-contain">
    <div id="content" class="site-content">
        <div class="wrap">
            <div id="primary" class="content-area">
                <main id="main" class="site-main">
                    <article id="post-<?php echo $post->ID; ?>" class="post-<?php echo $post->ID; ?> post type-plan2learn status-<?php echo $post->post_status; ?> hentry">
                        <div id="holdstart" class="p2lhold-container">
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
                            </style>
                            <h2 style="text-align: center;"><?php echo __('Holdstart','wk-plan2learn'); ?></h2>
                            <div class="bundles">
                                <div class="scbundle">
                                    <div class="scbundle_container">
                                		<div class="scbundle_header">
                                			<h3><?php echo $post->post_title;?>, <?php echo $start_year; ?></h3> 
                                			<span class="price pid262852" id="262852">
                                			    <span class="woocommerce-Price-amount amount">
                                			        <bdi><?php echo number_format($price, 0, ',','.'); ?>
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
                                	            <em class="icon-location tooltip"><img src="/wp-content/uploads/2018/03/pin.png"><a href="<?php echo $value[0]->LocationHomepage; ?>"><?php echo $value[0]->Room; ?></a>
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
                    </article>
                </main>
            </div>
        </div>
    </div>
</div>

<?php 

// echo '<pre style="background: #045860; color: white; ">';
// print_r($modules);
// echo '</pre>';

get_footer();