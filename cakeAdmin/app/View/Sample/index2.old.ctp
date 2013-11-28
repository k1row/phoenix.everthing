<?php $this->Html->script(array('jquery-1.9.1', 'jquery-ui-1.10.3.custom.min', 'jquery.jqGrid-4.5.2/src/jquery.jqGrid', 'jquery.jqGrid-4.5.2/src/jqModal', 'jquery.jqGrid-4.5.2/src/jqDnR'), array('inline'=>false)); ?>
<?php $this->Html->scriptStart(array('inline'=>false)); ?>

<?php echo $this->Html->css(array('style', 'ui.jqgrid', 'ui-lightness/jquery-ui-1.10.3.custom.min'), false, array('inline'=>false)); ?>


$(function() {
      $("input:submit, a, button", ".toolbar").button();


     $("#tabs").bind('handleTabSelect', function(event, ui) {
             var active = $("#tabs").tabs("option", "active");
//             alert($("#tabs ul>li a").eq(active).attr('href')); 

          //jQuery("#list").jqGrid('setGridParam', { url:"jqgrid?pid="+ui.panel.id, page:1 });

          jQuery("#list").trigger('reloadGrid');
      });


      // 「コンテンツ読み込み」ボタンのクリック処理
      // URL にページを指定する
      $("#load").click(function() {
          var curTabs =  $("#tabs").tabs("length") + 1;   // 現在のタブ数+1
          var selected = $("#tabs").tabs("option", "selected");
          var url = "index3";
          var title = curTabs + "番目のタブ";
          $("#tabs").tabs("add", url, title);
      });
  });

jQuery(document).ready(function(){

  var tabOpts = {
      activate: handleTabSelect,
      // fx has been deprecated
      show: {
          height: 'toggle',
          opacity: 'toggle'
      }
  };

  $("#tabs").tabs(tabOpts);
  function handleTabSelect(event, tab) {
        var publisher = tab.newPanel.selector.split ("_");
        new_url = "jqgrid?pid=" + publisher[1];
        //alert (new_url);
        jQuery("#list").jqGrid('setGridParam', { url:new_url, page:1 });
        jQuery("#list").trigger('reloadGrid');
  }

  $("input:submit, a, button", ".toolbar").button();

  jQuery("#list").jqGrid({
    url:'jqgrid',
    datatype: 'xml',
    colNames:['id','target_date','click_num','install_num','CVR'],
    colModel:[ {index:'id', name:'id', width: '40', align: 'center', editable:false, editoptions:{size:10, readonly:'readonly'}, hidden:false },
               {index:'target_date', name:'target_date', width: '160', editable:false, editoptions:{size:20} },
               {index:'click_num', name:'created', width: '140', align: 'right', editable:false },
               {index:'install_num', name:'modified', width: '140', align: 'right', editable:false },
               {index:'cvr', name:'cvr', width: '140', align: 'right', editable:false },
             ],
    rowNum:50,
    multiselect: false,
    loadComplete : function () {
         //$("#list").jqGrid('setGridWidth', $(map_canvas).width(), true); 
    },
    // 行の選択イベント
    onSelectRow: function(id) {
        if (id)
        {
          var row = jQuery("#list").jqGrid('getRowData', id);
          if (row)
          {
          }
        }
    },
    width: 'auto',
    height: 'auto',
    rowList:[10,20,30],
    sortname: 'id',
    sortorder: "desc",
    viewrecords: true,
    caption: 'My first grid'
  }); 
}); 

<?php $this->Html->scriptEnd(); ?>


<div id="tabs">
    <ul>
      <li><a href="#tabs_total">Total</a></li>
      <?php foreach ($publishers as $publisher): ?>
          <li><a href="#tabs_<?php echo $publisher['PublisherMaster']['id']; ?>"><?php echo $publisher['PublisherMaster']['owner_name']; ?></a></li>
      <?php endforeach; ?>
    </ul>

    <div id="tabs_total">
      <table id="list"></table> 
      <div id="pager" style="text-align:center;"></div> 
    </div>

    <?php foreach ($publishers as $publisher): ?>
      <div id="tabs_<?php echo $publisher['PublisherMaster']['id']; ?>">
        <table id="list"></table> 
        <div id="pager" style="text-align:center;"></div>
      </div>
    <?php endforeach; ?>

</div> <!-- end of tabs -->
