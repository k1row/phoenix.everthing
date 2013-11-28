<?php $this->Html->script(array('jquery-1.8.3.min', 'jquery-ui.min', 'jquery.jqGrid-4.5.2/src/jquery.jqGrid', 'jquery.jqGrid-4.5.2/src/jqModal', 'jquery.jqGrid-4.5.2/src/jqDnR'), array('inline'=>false)); ?>
<?php $this->Html->scriptStart(array('inline'=>false)); ?>

<?php echo $this->Html->css(array('style', 'ui.jqgrid', 'jquery-ui-1.9.0.custom.min'), false, array('inline'=>false)); ?>


function reloadGroupParent(target_date)
 {
    var result = true;

    if (target_date && target_date != '')
    {
        $.ajax({
            type: "GET",
            url: "jqgrid_getgroups?target_date=" + target_date,
            async: false,
            success: function(response, textStatus, xhr) {
                var grps = new Array();
                grps[''] = '';
                var items = eval(response);
                $.each(items, function(i, item) {
                    grps[item.group_id] = item.group_name;
                });
                $('#groups').setColProp('group_parent_id', { editoptions: { value: grps } });
            },
            error: function(res, textStatus, xhr) {
                result = false;
            }
        });
    } else {
        result = false;
    }
    return result;
}


jQuery(document).ready(function(){ 
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
              // グループ一覧の取得URLのセット
              //jQuery("#list").jqGrid('setGridParam', {url:"jqgrid_getgroups?target_date="+row.target_date,page:1});
              // 上位グループ名一覧の取得
              reloadGroupParent(row.target_date);

              // グループ一覧グリッド読み込み
              //jQuery("#groups").trigger('reloadGrid');
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


<table id="list"></table> 
<div id="pager" style="text-align:center;"></div> 
