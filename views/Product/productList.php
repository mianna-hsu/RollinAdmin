<?php

// 對應 header template nav active
$pageDir = "Product";
$pageTitle = "Product List";

// breadcrumb 中文化
$pageDirTW = "商品管理";
$pageTitleTW = "商品清單";


// get set querystring
parse_str($_SERVER['QUERY_STRING'], $query);

// set variables
$pageSize = isset($query["pageSize"]) ? $query["pageSize"] : 20;
$ProductName = isset($query["ProductName"]) ? $query["ProductName"] : "";
$pageNo = isset($query["pageNo"]) ? $query["pageNo"] : 1;
$show = isset($query["show"]) ? $query["show"] : "l";

// set table columns
$array_columns = [
  "p.ProductID" => "編<br>號",
  "p.ProductName" => "品名",
  "b.BrandName" => "品牌",
  "c.CategoryName" => "類別",
  "PDescription" => "詳細",
  "TotalStock" => "總庫存",
  "StockOnOrder" => "訂單量",
  "UnitPrice" => "單價",
  "Date" => "更新日期",
];

// get column, sort
$columns = array_keys($array_columns);
$column = isset($_GET['column']) && in_array($_GET['column'], $columns) ? $_GET['column'] : $columns[0];
$sort = isset($_GET['sort']) && strtolower($_GET['sort']) == 'desc' ? 'DESC' : 'ASC';

// set hidden inputs
$hidInputs = [
  'column' => $column,
  'sort' => $sort,
  'show' => $show
];

// set page size select potions
$array_pageSize = [3, 6, 20];

// for sorting
$up_or_down = str_replace(array('ASC','DESC'), array('up','down'), $sort); 
$asc_or_desc = $sort == 'ASC' ? 'desc' : 'asc';

// for view
$viewQuery = "Product/List?pageSize=$pageSize&ProductName=$ProductName&column=$column&sort=$sort&pageNo=$pageNo";

// for pagination
$pageStartIndex = ($pageNo - 1) * $pageSize;
$ProductsTotal = $data->getAllCount()->Total;
$ProductsCount = $ProductName == "" ? $ProductsTotal : $data->getAllLikeCount($ProductName)->Count;
$pagesCount = ceil((int) $ProductsCount / (int) $pageSize);


#read data_拿資料________________________________
// $products = $data->getAll();
$products = $data->getAllLike($ProductName, $column, $sort, $pageStartIndex, $pageSize);
#______________________________________

function createPagination($pagesCount, $pageNo, $query) {
  if ($pagesCount > 1) {
    $pageQuery = $query;
    unset($pageQuery['url']);

    $prevousNo = $pageNo - 1;
    $pageQuery['pageNo'] = $prevousNo;
    $prevousQueryString = http_build_query($pageQuery, '', '&');
    $prevousDisabled = $prevousNo <= 0 ? "disabled" : "";

    $nextNo =  $pageNo + 1;
    $pageQuery['pageNo'] = $nextNo;
    $nextQueryString = http_build_query($pageQuery, '', '&');
    $nextDisabled = $nextNo > $pagesCount ? "disabled" : "";

    echo "<nav aria-label='Page navigation'>";
    echo "<ul class='pagination'>";
    echo "<li class='page-item $prevousDisabled'><a class='page-link' href='./Product/List?$prevousQueryString'>上一頁</a></li>";

    for ($i = 1; $i <= $pagesCount; $i++) {
      $pageQuery['pageNo'] = $i;
      $queryString = http_build_query($pageQuery, '', '&');
      $active = ($pageNo == $i) ? "active" : "";
      echo "<li class='page-item $active'><a class='page-link' href='./Product/List?$queryString'>$i</a></li>";
    }

    echo "<li class='page-item $nextDisabled'><a class='page-link' href='./Product/List?$nextQueryString'>下一頁</a></li>";
    echo "</ul>";
    echo "</nav>";
  }
}
# ----------------------------------------------------------
require_once 'views/template/header.php';

?>
<style>
th>a {
  text-decoration:none;
  color:#000;
}

#ProductID{
  padding:3px;
  width:3%;
}

#ProductName{
  width:15%;
}

/* .td-w {
  width: 170px;
}
.list-image {
  width: 150px;
  height: 150px;
}
.img-thumbnail-wrap {
  width: 150px;
  height: 150px;
  margin: 1px; */
}
</style>


<div class="container-fluid">
  <div class="card">
    <div class="card-header">
      
    <form name="form" method="get" action="">
        <div class="form-row">
          <div class="col-3">
            <div class="form-group row">
              <label for="pageSize" class="col-6 col-form-label">每頁顯示筆數</label>
              <div class="col-6">
                <select name="pageSize" class="form-control" onchange="this.form.submit()">
                  <?php
                  foreach ($array_pageSize as $thisSize) {
                    $selected = $pageSize == $thisSize ? "selected" : "";
                    echo "<option value='$thisSize' $selected>$thisSize</option>";
                  }
                  ?>
                </select>
              </div>
            </div>
          </div>
          <div class="col-3">
            <label class="col-form-label">
              <?= "總共有：$ProductsTotal 項商品" ?> 
            </label>
          </div>
          <div class="col-2">
            <input type="text" name="ProductName" class="form-control float-right" placeholder="輸入商品名稱搜尋" value="<?= $ProductName ?>"  onchange="this.form.submit()">

          </div>
          <div class="col-2">
          <input type="submit" class="btn btn-dark" value="搜尋" name="searchButton">
            <label class="col-form-label" style="display: ;">
              <?= "搜尋到：$ProductsCount 個項目" ?> 
            </label>
          </div>
        </div>
        <?php 
          foreach ($hidInputs as $k => $v) {
            echo "<input type='hidden' name='$k' value='$v'>";
          }
        ?>
        
    </form>
      <!-- /.form-row -->
    </div>
    <!-- /.card-header -->
    <div class="card-body">
    <?php createPagination($pagesCount, $pageNo, $query); ?>
    <div class=""> <!-- button -->
        <input type="button" class="btn btn-outline-info" id="checkedRevBtn" value="反選">
        <input type="submit" class="btn btn-outline-danger" id="checkedDeleteBtn" name="checkedDeleteBtn" value="勾選下架" onclick="return confirm('是否確認刪除勾選資料')">
      </div>
      <table id="listTable" class="table table-bordered table-hover">
        <thead>
          <tr>
          <th><input type="checkbox" id="selectallcheckbox" onclick="SALLcheckbox()"></th>
          <?php
                foreach($array_columns as $col => $colTW) {
                  $queryString = "pageSize=$pageSize&ProductName=$ProductName&column=$col&sort=$asc_or_desc&pageNo=$pageNo";
                  $sortClass =$column == $col ? "-" . $up_or_down : "";
                  echo "<th id='$col'>";
                  echo "<a href='/RollinAdmin/Product/List?$queryString'>$colTW<i class='fas fa-sort$sortClass'></i></a>";
                  echo "</th>";
                }
                ?>            
<!-- 
            <th style='width:2%'>ID</th>
            <th style='width:15%'>Name</th>
            <th>Brand</th>
            <th>Category</th>
            <th>Description</th>
            <th style='width:7%'>總庫存</th>
            <th style='width:7%'>訂單量</th>
            <th>單價</th>
            <th>更新日期</th> Date -->
          </tr>
        </thead>
        <tbody>
          <?php
          $Productsubtitle = "";
          foreach ($products as $pd) {
            if($pd->Discontinued==1){
              $Productsubtitle = "(未上架)";
            }
            //  onclick=\"window.location='/RollinAdmin/Product/Detail/" . $pd->ProductID . "'\"
            echo "<tr ondblclick=\"displayMore('$pd->ProductID');\">";
            echo '<td><input type="checkbox" name="checkbox" id="checkbox' . $pd->ProductID . '" ></td>';
            echo "<td>" .$pd->ProductID."</td>";
            echo "<td><a href=/RollinAdmin/Product/Detail/$pd->ProductID>".$pd->ProductName."</a>".$Productsubtitle."</td>";
            echo "<td>".$pd->BrandName."</td>";
            echo "<td>$pd->CategoryName</td>";
            echo "<td>" . substr($pd->PDescription,0,69) . "</td>";
            echo "<td>$pd->TotalStock</td>";
            echo "<td>002</td>";
            echo "<td>$pd->UnitPrice</td>";
            echo "<td>$pd->Date</td>";
            echo "</tr>";
            //點擊顯示下拉庫存
                  $Stocks = $data->getStock($pd->ProductID);
                  foreach ($Stocks as $pdst) {
                    $SizeName ="One Size" ;
                    if(isset($pdst->SizeName)){
                      $SizeName = "$pdst->SizeName";
                    }
                    echo  <<<here
                    <tr name='$pd->ProductID' style='display: none;' >
                    <td bgcolor="#778899"></td>
                    <td bgcolor="#778899"></td>
                    <td bgcolor="#778899"></td>
                    <td bgcolor="#778899">Size:$SizeName </td>
                    <td bgcolor="#778899">Color:$pdst->Color </td>
                    <td bgcolor="#778899"></td>
                    <td bgcolor="#778899">pdst:$pdst->UnitInStock</td>
                    <td bgcolor="#778899"></td>
                    <td bgcolor="#778899"></td>
                    <td bgcolor="#778899"></td>
                    </tr>
                    here;
                  }
              
          }
          ?>
        </tbody>
        <tfoot>
        </tfoot>
      </table>
    </div>
    <!-- /.card-body -->
  </div>
  <!-- /.card -->
</div>
<!-- /.container-fluid -->


<?php

require_once 'views/template/footer.php';

?>
<script>
// 點擊顯示詳細庫存
function displayMore(TagName){
 var obj = document.getElementsByName(TagName);
 for(var item in obj){
    if(obj[item].style.display==""){
    obj[item].style.display = "none";
    }else{
    obj[item].style.display = "";
    }
 };
}
var obj = document.getElementsByName("checkbox");
// checkbox點擊全選或取消
function SALLcheckbox() {
  var selectallcheckbox = document.getElementById("selectallcheckbox").checked;
    for (var i = 0; i < obj.length; i++) {
      if (selectallcheckbox == true){
        obj[i].checked = true;
      } else{
        obj[i].checked = false;
      }
    }
}
//反選按鈕

let checkedRevBtn = document.getElementById("checkedRevBtn");
    checkedRevBtn.onclick = function() {
      for (var j = 0; j < obj.length; j++) {
        if (obj[j].checked) {
          obj[j].checked = false;
        } else {
          obj[j].checked = true;
        }
      }
    }
</script>