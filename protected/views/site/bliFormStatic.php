<?php

Yii::app()->clientScript->registerScript("bliFormJS", "
    var queryParams = window.location.search;
    console.log(queryParams);
    $('#sign-btn').on('click', function(evt) {
        evt.preventDefault();
        window.location.replace('https://staging.tworld.x2developer.com/index.php/site/finishBli'+queryParams);
    })
");

?>

<html>
    <head>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.1/jquery.min.js"></script>
        <script src="https://kit.fontawesome.com/ae0f5ce4a3.js" crossorigin="anonymous"></script>
        <style>
            .required:after {
                content:" *";
                color: red;
            }

            .item-title {
                color: #00461C;
            }

            .item-text {
                color: black;
            }
        </style>
    </head>
    <body>
        <div class="container d-flex">
            <form class="w-100">
                <div class="row w-100 d-flex justify-content-between mb-3">
                    <div class="col-auto">
                        <img style="height: 125px;" src="https://dedupe.sydney.x2developer.com/tworld.png"></img>
                    </div>
                    <div class="d-flex align-items-end col-auto">
                        <h3 class="item-title">Listing Number: <span class="item-text"><?php echo $listing->c_listing_number__c; ?></span></h3>
                    </div>
                </div>
                <div class="border border-2" style="border-color: #00461C !important"></div>
                <div class="border border-2"></div>
                <div class="row w-100 d-flex my-4">
                    <div class="col-6">
                        <h4 class="item-title"><?php echo $listing->name; ?></h4>
                        <div>
                            <h5><?php echo $listing->c_address; ?></h5>
                            <h5><?php echo $listing->c_city__c . ", " . $listing->c_state__c . " " . $listing->c_postalcode; ?></h5>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="item-title fw-bold">Industry Type: <span class="item-text fw-normal"><?php echo strtr($listing->c_category__c, array('"'=>'','['=>'',']'=>'')); ?></span></div>
                        <div class="item-title fw-bold">Industry Detail: <span class="item-text fw-normal"><?php echo substr($listing->c_subcategory_1__c, 0, strrpos($listing->c_subcategory_1__c, '_', 0)); ?></span></div>
                    </div>
                </div>
                <hr>
                <div class="row w-100 d-flex my-4">
                    <div class="col-6">
                        <div class="item-title fw-bold">Price: <span class="item-text fw-normal">$<?php echo number_format($listing->c_listing_price__c, 2); ?></span></div>
                        <div class="item-title fw-bold">Down Payment: <span class="item-text fw-normal">$<?php echo number_format($listing->c_down_payment_requested__c, 2); ?></span></div>
                    </div>
                    <div class="col-6">
                        <div class="item-title fw-bold">Seller Financing*: <span class="item-text fw-normal">$<?php echo number_format($listing->c_owner_financing_terms__c, 2); ?></span></div>
                        <div class="item-title fw-bold">Seller Financing Rate*: <span class="item-text fw-normal"><?php echo isset($listing->c_owner_financing_interest__c) ? $listing->c_owner_financing_interest__c : '0'; ?>%</span></div>
                        <div>*Financing may be available for qualified buyers only</div>
                    </div>
                </div>
                <div class="border border-2" style="border-color: #00461C !important"></div>
                <div class="row w-100 d-flex my-4 justify-content-between">
                    <div class="col-auto">
                        <h4 class="item-title"><?php echo $listing->c_ad_headline__c; ?></h4>
                        <p><?php echo $listing->c_business_description__c; ?></p>
                    </div>
                </div>
                <hr>
                <div class="row w-100 d-flex my-4 justify-content-between">
                    <div class="w-100">
                        <div class="p-2 fw-bold" style="color: white;background-color: #00461C;">Asset Information</div>
                        <div class="border border-2"></div>
                        <table class="table w-100">
                            <thead>
                                <tr>
                                    <th class="item-title" scope="col">Accounts Receivable:</th>
                                    <th class="item-title" scope="col">FF and E:</th>
                                    <th class="item-title" scope="col">Lease Improvements:</th>
                                    <th class="item-title" scope="col">Inventory:</th>
                                    <th class="item-title" scope="col">Real Estate:</th>
                                    <th class="item-title" scope="col">Total Assets:</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td scope="row">
                                        <div>
                                            $<?php echo number_format($listing->c_accounts_receivable, 2); ?>
                                        </div>
                                        <div>
                                            <?php if ($listing->c_accounts_receivable_incl): ?>
                                                <i class="fa fa-check"></i>
                                            <?php else: ?>
                                                <i class="fa fa-times"></i>
                                            <?php endif; ?>
                                            Included
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            $<?php echo number_format($listing->c_ff_e_value__c, 2); ?>
                                        </div>
                                        <div>
                                            <?php if ($listing->c_ff_e_included): ?>
                                                <i class="fa fa-check"></i>
                                            <?php else: ?>
                                                <i class="fa fa-times"></i>
                                            <?php endif; ?>
                                            Included
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            $<?php echo number_format($listing->c_leasehold_improvements__c, 2); ?>
                                        </div>
                                        <div>
                                            <?php if ($listing->c_leasehold_improvements_incl): ?>
                                                <i class="fa fa-check"></i>
                                            <?php else: ?>
                                                <i class="fa fa-times"></i>
                                            <?php endif; ?>
                                            Included
                                        </div>
                                    </td>
                                    <td>
                                       <div>
                                            $<?php echo number_format($listing->c_inventory_value__c, 2); ?>
                                        </div>
                                        <div>
                                            <?php if ($listing->c_Inventory_Included_c): ?>
                                                <i class="fa fa-check"></i>
                                            <?php else: ?>
                                                <i class="fa fa-times"></i>
                                            <?php endif; ?>
                                            Included
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            $<?php echo number_format($listing->c_RealEstateValue, 2); ?>
                                        </div>
                                        <div>
                                            <?php if ($listing->c_real_estate_included__c): ?>
                                                <i class="fa fa-check"></i>
                                            <?php else: ?>
                                                <i class="fa fa-times"></i>
                                            <?php endif; ?>
                                            Included
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            $<?php echo number_format($listing->c_total_assets__c, 2); ?>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="row w-100 d-flex my-4">
                    <div class="col-6">
                        <div class="item-title fw-bold">Building Type: <span class="item-text fw-normal"><?php echo $listing->c_type_of_location__c; ?></span></div>
                        <div class="item-title fw-bold">Lease/Month: <span class="item-text fw-normal">$<?php echo number_format($listing->c_monthly_rent__c, 2); ?></span></div>
                    </div>
                    <div class="col-6">
                        <div class="item-title fw-bold">Sq Ft <span class="item-text fw-normal"><?php echo $listing->c_square_footage__c; ?></span></div>
                        <div class="item-title fw-bold">Terms &amp; Options <span class="item-text fw-normal"><?php echo $listing->c_terms_options__c; ?></span></div>
                    </div>
                </div>
                <div class="border border-1" style="border-color: #00461C !important"></div>
                <div class="row w-100 d-flex my-4 justify-content-between">
                    <div class="item-title col-auto fw-bold">
                        Business is:
                    </div>
                    <div class="col-auto">
                        <div>
                            <?php if ($listing->c_relocatable): ?>
                                <i class="fa fa-check"></i>
                            <?php else: ?>
                                <i class="fa fa-times"></i>
                            <?php endif; ?>
                        </div>
                        <div>Relocatable</div>
                    </div>
                    <div class="col-auto">
                        <div>
                            <?php if ($listing->c_franchisee_operation__c): ?>
                                <i class="fa fa-check"></i>
                            <?php else: ?>
                                <i class="fa fa-times"></i>
                            <?php endif; ?>
                        </div>
                        <div>Franchise</div>
                    </div>
                    <div class="col-auto">
                        <div>
                            <?php if ($listing->c_lender_prequalified__c): ?>
                                <i class="fa fa-check"></i>
                            <?php else: ?>
                                <i class="fa fa-times"></i>
                            <?php endif; ?>
                        </div>
                        <div>Lender Pre-Qualified</div>
                    </div>
                    <div class="col-auto">
                        <div>
                            <?php if ($listing->c_home_based): ?>
                                <i class="fa fa-check"></i>
                            <?php else: ?>
                                <i class="fa fa-times"></i>
                            <?php endif; ?>
                        </div>
                        <div>Home Based</div>
                    </div>
                </div>
                <div class="row w-100 d-flex my-4">
                    <div class="col-6">
                        <table class="table table-borderless caption-top">
                            <caption><div class="p-2 fw-bold" style="color: white;background-color: #00461C;">Seller's Financial Representations</div><div class="border border-2"></div></caption>
                            <tbody>
                                <tr><td class="w-50 fw-bold"><?php echo $listing->c_data_source__c; ?></td><td><?php echo $listing->c_data_year__c; ?></td></tr>
                                <tr><td class="fw-bold">Total Sales</td><td>$<?php echo number_format($listing->c_total_sales__c, 2); ?></td></tr>
                                <tr><td class="fw-bold">COGS</td><td>$<?php echo number_format($listing->c_cost_of_goods_sold__c, 2); ?></td></tr>
                                <tr><td class="fw-bold">Total Expenses</td><td>$<?php echo number_format($listing->c_total_expenses__c, 2); ?></td></tr>
                                <tr><td class="fw-bold">Net Income</td><td>$<?php echo number_format($listing->c_net_income__c, 2); ?></td></tr>
                                <tr><td class="fw-bold">Owners Salary</td><td>$<?php echo number_format($listing->c_owner_s_salary__c, 2); ?></td></tr>
                                <tr><td class="fw-bold">Beneficial AddBacks</td><td>$<?php echo number_format($listing->c_beneficial_addbacks__c, 2); ?></td></tr>
                                <tr><td class="fw-bold">Interest</td><td>$<?php echo number_format($listing->c_interest__c, 2); ?></td></tr>
                                <tr><td class="fw-bold">Depreciation</td><td>$<?php echo number_format($listing->c_deprecation__c, 2); ?></td></tr>
                                <tr><td class="fw-bold">Other</td><td>$<?php echo number_format($listing->c_other__c, 2); ?></td></tr>
                                <tr><td class="fw-bold">SDE</td><td>$<?php echo number_format($listing->c_seller_discretionary_earnings__c, 2); ?></td></tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="col-6">
                        <table class="table table-borderless caption-top">
                            <caption><div class="p-2 fw-bold" style="color: white;background-color: #00461C;">Additional Information</div><div class="border border-2"></div></caption>
                            <tbody>
                                <tr><td class="w-50 fw-bold">Reason For Sale:</td><td><?php echo $listing->c_reason_for_sale__c; ?></td></tr>
                                <tr><td class="fw-bold">Hours Of Operation:</td><td><?php echo $listing->c_business_hours_of_operation__c; ?></td></tr>
                                <tr><td class="fw-bold">Hours Owner Works:</td><td><?php echo $listing->c_owner_weekly_hours__c; ?></td></tr>
                                <tr><td class="fw-bold">Year Established:</td><td><?php echo $listing->c_year_established__c; ?></td></tr>
                                <tr><td class="fw-bold">Years Owned:</td><td><?php echo $listing->c_years_owned__c; ?></td></tr>
                                <tr><td class="fw-bold">Employees FT:</td><td><?php echo $listing->c_of_employees_ft__c; ?></td></tr>
                                <tr><td class="fw-bold">Employees PT:</td><td><?php echo $listing->c_of_employees_pt__c; ?></td></tr>
                                <tr><td class="fw-bold">Managers:</td><td><?php echo $listing->c_number_of_employees_mgrs__c; ?></td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <hr>
                <div class="row w-100 d-flex my-4 justify-content-between">
                    <div class="p-2 fw-bold" style="color: white;background-color: #00461C;">Transworld Business Advisors of <?php echo substr($listing->c_franchisee__c, 0, strpos($listing->c_franchisee__c, '_', 0)); ?></div>
                    <div class="mb-3 border border-2"></div>
                    <div class="col-auto">
                        <div class="fw-bold"><?php echo $employee->name; ?></div>
                        <div><?php echo $office->c_street_address__c; ?></div>
                        <div><?php echo $office->c_city__c . ", " . $office->c_state__c; ?></div>
                        <div><?php echo $office->c_zipcode__c; ?></div>
                    </div>
                    <div class="col-auto">
                        <div class="fw-bold">Office: <span class="fw-normal"><?php echo $office->c_phone__c; ?></span></div>
                        <div class="fw-bold">Direct: <span class="fw-normal"><?php echo $employee->c_phone__c; ?></span></div>
                        <div class="fw-bold">Mobile: <span class="fw-normal"><?php echo $employee->c_cell_phone__c; ?></span></div>
                    </div>
                    <div class="col-auto">
                        <div class="fw-bold">Fax: <span class="fw-normal"><?php echo $employee->c_fax__c; ?></span></div>
                        <div class="fw-bold">Email: <span class="fw-normal"><?php echo $employee->c_email__c; ?></span></div>
                        <div class="fw-bold">Home Page: <span class="fw-normal"><a href="<?php echo "https://tworld.com/agent/" . $employee->c_site_url__c; ?>" target="_blank"><?php echo "https://tworld.com/agent/" . $employee->c_site_url__c; ?></a></span></div>
                    </div>
                </div>
                <div class="p-2 border border-2 row w-100 d-flex my-4 justify-content-between">
                    <div class="w-100">
                        <div>
                            <p>Receipt of confidential business information received by <?php echo $buyer->firstName . " " .  $buyer->lastName; ?> is acknowledged by signature below</p>
                        </div>
                        <div class="col-6">Date: <?php echo date("m/d/Y", time()); ?></div>
                        <div class="col-6">By: <button id="sign-btn" class="btn btn-sm btn-primary">Sign Here</button></div>
                    </div>
                </div>
                <div class="row w-100 d-flex my-4 justify-content-between">
                    <div class="col-auto">
                        <p style="font-size: 0.75rem;">
                            The seller provides all data and financial information on this business for informational purposes only. The broker does not warrant the above information and advises the buyer to seek professional advice when purchasing a business. This offering by the seller is subject to change or withdrawal without notice. This information sheet has been prepared on a confidential basis exclusively for: <?php echo $buyer->firstName . " " .  $buyer->lastName; ?>
                        </p>
                    </div>
                </div>
            </form>
        </div>
    </body>
</html>
