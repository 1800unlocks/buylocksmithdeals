<div class="wrap">
    <div class="container">
        <h1>Reports Dashboard</h1>
        <div class="inner_area report_inner_area">
        
            <div class="show_count_area_main">
                <div class="show_count_area">
                    <div class="count_title">Total Vendor</div>
                    <div class="icon-count"><span class="dashicons dashicons-businessman"></span></div>
                    <div class="count_number"><?php echo BuyLockSmithDealsCustomizationAdminReport::totalVendors();?></div>
                    
                </div>
                
                
            </div>
            <div class="show_count_area_main">
                <div class="show_count_area">
                    <div class="count_title">Total Customer</div>
                    <div class="icon-count"><span class="dashicons dashicons-admin-users"></span></div>
                    <div class="count_number"><?php echo BuyLockSmithDealsCustomizationAdminReport::totalCustomer();?></div>
                    
                </div>
                
                
            </div>
            
            <div class="show_count_area_main">
                <div class="show_count_area">
                    <div class="count_title">Total Deals</div>
                    <div class="icon-count"><span class="dashicons dashicons-cart"></span></div>
                    <div class="count_number"><?php echo BuyLockSmithDealsCustomizationAdminReport::totalDeals();?></div>
                    
                </div>
                
                
            </div>
            
        </div>
        <?php
        $blsd_yearList = BuyLockSmithDealsCustomizationAdminReport::blsd_yearList();
        $current_year = date('Y');
        if(isset($_REQUEST['year'])){
            $current_year = $_REQUEST['year'];
        }
        ?>
        <div class="inner_area report_inner_area">
            <div class="select_year">
                <select name="select_year" onchange="updateSelectData(jQuery(this))">
                    <?php
                            foreach ($blsd_yearList as $listData){
                                $selected = '';
                                if($current_year==$listData){
                                     $selected = 'selected';
                                }
                                echo "<option $selected value='$listData'>$listData</option>";
                            }
                    ?>
                    
                </select>
            </div>
            <div class="report-inner-area-2-column">
            <div class="char_area_part">
         <canvas id="bar-chart" width="500" height="300"></canvas>
         </div>
            <div class="char_area_part float_area_right">
         <canvas id="bar-chart-customer" width="500" height="300"></canvas>
         </div>
                </div>
        </div>
<!--        <div class="inner_area report_inner_area">
            <div class="char_area_part_full">
         <canvas id="line-chart" width="500" height="300"></canvas>
         </div>
          
          
        </div>-->
    </div>
</div>
<?php
$totalVendorIdListByMonth = BuyLockSmithDealsCustomizationAdminReport::totalVendorIdListByMonth($current_year);
$totalCustomerIdListByMonth = BuyLockSmithDealsCustomizationAdminReport::totalCustomerIdListByMonth($current_year);



?>

<script>
    var monthData = '<?php echo json_encode($totalVendorIdListByMonth);?>';
    var monthDataCustomer = '<?php echo json_encode($totalCustomerIdListByMonth);?>';
    monthData = JSON.parse(monthData);
    monthDataCustomer = JSON.parse(monthDataCustomer);
// Bar chart
new Chart(document.getElementById("bar-chart"), {
    type: 'bar',
    data: {
      labels: ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"],
      datasets: [
        {
          label: "Vendors",
          backgroundColor: ["#3e95cd", "#3e95cd","#3e95cd","#3e95cd","#3e95cd", "#3e95cd", "#3e95cd","#3e95cd","#3e95cd","#3e95cd","#3e95cd","#3e95cd"],
          data: monthData
        }
      ]
    },
    options: {
      legend: { display: false },
      title: {
        display: true,
        text: 'Vendor Registration'
      }
    }
});
new Chart(document.getElementById("bar-chart-customer"), {
    type: 'bar',
    data: {
      labels: ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"],
      datasets: [
        {
          label: "Customer",
          backgroundColor: ["#3e95cd", "#3e95cd","#3e95cd","#3e95cd","#3e95cd", "#3e95cd", "#3e95cd","#3e95cd","#3e95cd","#3e95cd","#3e95cd","#3e95cd"],
          data: monthDataCustomer
        }
      ]
    },
    options: {
      legend: { display: false },
      title: {
        display: true,
        text: 'Customer Registration'
      }
    }
});
//
//new Chart(document.getElementById("line-chart"), {
//    type: 'line',
//    data: {
//      labels: ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"],
//      datasets: [
//        {
//          label: "Deals",
//         
//          data: monthDataCustomer
//        }
//      ]
//    },
//    options: {
//      legend: { display: false },
//      title: {
//        display: true,
//        text: 'Deals Sold'
//      },
//      elements: {
//            line: {
//               
//                fill: false,
//                borderColor: '#ffb900',
//            }
//        }
//    }
//});

function updateSelectData(that){
   var year = that.val();
   if(year!=''){
       window.location.href = '<?php echo home_url();?>'+'/wp-admin/admin.php?page=blsm-reports-dashboard&year='+year;
   }
}

</script>
