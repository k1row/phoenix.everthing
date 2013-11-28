<?php $this->Html->script(array('jquery-1.8.3.min', 'jquery-ui.min'), array('inline'=>false)); ?>
<?php $this->Html->scriptStart(array('inline'=>false)); ?>


// ������ւ��jQurey
   $( function() {
      $("*[name=btn]").click(
         function() {
            var $keyword = '';
            $.ajax({
               url: "./getAr",
               type: "POST",
               data: { val : $keyword },  // �����Ȃǈ�����n���K�v������Ƃ�������g��
               //dataType: 'json',        // �T�[�o�[�Ȃǂ̊��ɂ���Ă��̃I�v�V�������K�v�ȂƂ�������
               success: function(arr) {
                     // �����̊����ƋA���Ă����z����p�X���Ȃ��Ƃ��߁B
                     // ���[�J�����Ƃ��̂܂܎g�����B
                     var parseAr = JSON.parse( arr );
                      $("p").text('�������ʁF'+ parseAr.length+'��');
                  reWriteTable(parseAr);
               }
            });
         }
      );
   });
   // �e�[�u��������������֐�
   function reWriteTable( response )
   {
      // ���ɂ���s���폜
      $("#list tr").remove();
      // �擾�����f�[�^���s�ɓ����
      for (var i=0; i< response.length; i++) {
         $("#list").append(
             $('<tr>').append(
                 $('<td class="name">').text(response[i]['name'])
             ).append(
                 $('<td class="age">').text(response[i]['age'])
             ).append(
                 $('<td class="address">').text(response[i]['address'])
             )
         );
      }
   }

<?php $this->Html->scriptEnd(); ?>


<body>
   <div><a href="javascript:void(0)" name="btn">send</a></div><br/>
   <p>Return</p>
   <table id="list" border="1">
      <tr><td class="name">aa</td><td class="age">7</td><td class="address">CA</td></tr>
      <tr><td class="name">bb</td><td class="age">5</td><td class="address">CC</td></tr>
      <tr><td class="name">cc</td><td class="age">13</td><td class="address">CB</td></tr>
   </table>
   <br/>
</body>

