<?php
Yii::app()->clientScript->registerCss('propActivityCss', "
div {
    font-family: -apple-system,system-ui,BlinkMacSystemFont,'Segoe UI',Roboto,'Helvetica Neue',sans-serif;
    color: #50596c;
}

h5,.h4,h3 {
    color: #50596c;
    font-family: -apple-system,system-ui,BlinkMacSystemFont,'Segoe UI',Roboto;
    font-weight: 700;
    letter-spacing: .05rem;
    line-height: 1.5;
}

.panel {
    border: .05rem solid #e7e9ed;
    border-radius: .2rem;
    display: flex;
    display: -ms-flexbox;
    -ms-flex-direction: column;
    flex-direction: column;
}

.column {
    flex: 1;
    max-width: 100%;
    padding-left: .4rem;
    padding-right: .4rem;
}

.card-label {
    color: gray;
    display: block;
    font-size: .6rem;
    font-weight: 700;
    line-height: 1rem;
    padding: 0.2rem 0;
    text-transform: uppercase;
    text-align: center;
}

.event-timeline-icon:before {
    border: .1rem solid #00b8d4;
    border-radius: 50%;
    content: '';
    display: block;
    height: .5rem;
    left: .5rem;
    top: .5rem;
    width: .5rem;
    position: absolute;
} 

.event-timeline-icon {
    border-radius: 50%;
    color: #fff;
    display: block;
    height: 1.2rem;
    text-align: center;
    width: 1.2rem;
}

.event-timeline-left {
    display: flex;
    display: -ms-flexbox;
    margin-bottom: 1.2rem;
    position: relative;
}   

.event-timeline-left:before {
    background: #e7e9ed;
    content: '';
    height: 100%;
    left: 11px;
    top: 1.2rem;
    width: 2px;
    position: absolute;
}

.text-lg {
    font-size: 1.2em;
}

.text-bold {
    font-weight: 700;
}
");
    // Activity Tab Count Code Start
    $listingsId=$_GET['id'];
    Yii::app()->clientScript->registerScript('activityTabCount',"

    $( document ).ready(function() {
        calculateTabValues();
    });

    function calculateTabValues(){
        var divIdArray=['ndaSent','ndaSigned'];
        var statusArray=[2,4];
        for(div in divIdArray){
            countOfTab($listingsId,statusArray[div],divIdArray[div]);
        }
        getBliCount($listingsId);
        getTotalOffersMade($listingsId);
        getCimSent($listingsId);
        getOffersMadeByBuyer($listingsId);
    }

    function getTotalOffersMade(listId){
        $.ajax({
            url:yii.scriptUrl + '/listings2/GetTotalOffersMade/id/' + listId,
            type: 'GET',
            success: function(data) {
                $('#totalOffersMade').text(data);
            },
            error: function(error) {
                console.log(error);
            }
        });

    }

    function getCimSent(listId){
        $.ajax({
            url:yii.scriptUrl + '/listings2/GetCimSent/id/' + listId,
            type: 'GET',
            success: function(data) {
                $('#cimSent').text(data);
            },
            error: function(error) {
                console.log(error);
            }
        });

    }

    function getOffersMadeByBuyer(listId){
        $.ajax({
            url:yii.scriptUrl + '/listings2/GetOffersMadeByBuyer/id/' + listId,
            type: 'GET',
            success: function(data) {
                $('#offersMadeByBuyer').text(data);
            },
            error: function(error) {
                console.log(error);
            }
        });
    }

    function getBliCount(listId){
        $.ajax({
            url:yii.scriptUrl + '/listings2/GetBliSentCount/id/' + listId,
            type: 'GET',
            success: function(data) {
                console.log(data);
                $('#bliSent').text(data);
            },
            error: function(error) {
                console.log(error);
            }
        });

    }

    function countOfTab(listId,status,divId){
        $.ajax({
            url:yii.scriptUrl + '/listings2/GetNdaCount/id/' + listId + '?status=' + status,
            type: 'GET',
            success: function(data) {
                console.log(data);
                $('#'+divId).text(data);
            },
            error: function(error) {
                console.log(error);
            }
        });
    }
    ");
    // Activity Tab Count Code End
?>

<div class="container-fluid mt-2 ml-2">
    <div class="row d-flex text-center">
        <div class="column col-sm-3">
            <div class="panel">
                <div class="p-4 h4" id="ndaSent">
                    <?php echo $numOfNdaSent; ?>
                </div>
                <div class="py-2 bg-light border-top card-label">
                    NDA SENT
                </div>
            </div>
        </div>
        <div class="column col-sm-3">
            <div class="panel">
                <div class="p-4 h4" id="ndaSigned">
                     <?php echo $numOfNdaSigned; ?>
                </div>
                <div class="py-2 bg-light border-top card-label">
                    NDA SIGNED
                </div>
            </div>
        </div>
        <div class="column col-sm-3">
            <div class="panel">
                <div class="p-4 h4" id="bliSent">
                     <?php echo $numOfBliSent; ?>
                </div>
                <div class="py-2 bg-light border-top card-label">
                    BLI SENT
                </div>
            </div>
        </div>
    </div>
</div>
<div class="container-fluid mt-2 ml-2">
    <div class="row d-flex text-center">
        <div class="column col-sm-3">
            <div class="panel">
                <div class="p-4 h4" id="totalOffersMade">
                    1
                </div>
                <div class="py-2 bg-light border-top card-label">
                    Number of Offers Made
                </div>
            </div>
        </div>
        <div class="column col-sm-3">
            <div class="panel">
                <div class="p-4 h4" id="cimSent">
                    1
                </div>
                <div class="py-2 bg-light border-top card-label">
                   Number of CIM Sent
                </div>
            </div>
        </div>
        <div class="column col-sm-3">
            <div class="panel">
                <div class="p-4 h4" id="offersMadeByBuyer">
                    1
                </div>
                <div class="py-2 bg-light border-top card-label">
                Offers Made By Buyer
                </div>
            </div>
        </div>
    </div>
</div>
