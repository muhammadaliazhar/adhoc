<?php
    $listing = Listings2::model()->findByAttributes(array('nameId' => $data->c_listinglookup__c));
    $listingUrl = null;
    $listingName = null;
    if (isset($listing) && $listing instanceof Listings2) {
        $listingUrl = $listing->c_listing_url__c;
        $listingName = $listing->name;
    }
    $phoneRaw = strtr($data->phone, array('(', ')', '-'));
    $mobileRaw = strtr($data->c_mobilephone, array('(', ')', '-'));

?>
<div class="" style="border: 1px solid #ccc;border-radius: 1.25rem; background-color: white;">
    <div class="container prop-item-container pt-3" style="padding-left: 0px; padding-right: 0px;">
        <div class="d-flex flex-column flex-md-row row justify-content-around align-items-md-center px-3">
            <div class="col-auto px-3" style="min-height: 100px;">
                <div class="row pb-3">
                    <div class="col-8">
                        <h6 class="font-weight-bold"><?php echo $data->name; ?></h6>
                        <div style="font-size: 10px;color: #979191;"><?php echo $data->c_ttl_city__c . ", " . $data->c_ttl_state_province__c ?></div>
                    </div>
                    <div id="seller-view-btn" class="d-flex justify-content-end col-4">
                        <button type="button" class="btn btn-view" style=""><a href="<?php echo Yii::app()->getBaseUrl(true) . "/index.php/contacts/" . $data->id ?>">VIEW</a></button>
                    </div>
                </div>
                <div class="row justify-content-between">
                    <div class="col-3" style="margin-bottom: 0.5rem;"><label style="font-size: 8px;color: #979191;display: block; margin-bottom: 0;">Phone</label><a href="tel:<?php echo $phoneRaw ?>" style="font-size: 8px;"><?php echo $data->phone ?></a></div>
                    <div class="col-3" style="margin-bottom: 0.5rem;"><label style="font-size: 8px;color: #979191;display: block; margin-bottom: 0;">Mobile</label><a href="tel:<?php echo $mobileRaw ?>" style="font-size: 8px;"><?php echo $data->c_mobilephone ?></a></div>
                    <div class="col-auto" style="margin-bottom: 0.5rem;"><label style="font-size: 8px;color: #979191;display: block; margin-bottom: 0;">Email</label><a href="mailto:<?php echo $data->email ?>" style="font-size: 8px;"><?php echo $data->email ?></a></div>
                </div>
            </div>
        </div>
    </div>
    <div class="d-flex d-grid justify-content-center" style="border-bottom-right-radius: 1.25rem;border-bottom-left-radius: 1.25rem;background-color: #f2f3f5;">
            <div style="margin:0.5rem;"><span class="h6 font-weight-bold mr-2" style="font-size: 10px;color: #979191;">Listing:</span><a style="font-size: 10px;" href="<?php echo $listingUrl ?>"><?php echo $listingName ?></a></div>
        </div>
</div>
<br />

