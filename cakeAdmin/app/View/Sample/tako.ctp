<?php $this->Html->script(array('jquery-1.8.3.min', 'jquery-ui.min'), array('inline'=>false)); ?>
<?php $this->Html->scriptStart(array('inline'=>false)); ?>


// ここらへんはjQurey
   $( function() {
      $("*[name=btn]").click(
         function() {
            var $keyword = '';
            $.ajax({
               url: "./getAr",
               type: "POST",
               data: { val : $keyword },  // 検索など引数を渡す必要があるときこれを使う
               //dataType: 'json',        // サーバーなどの環境によってこのオプションが必要なときがある
               success: function(arr) {
                     // 自分の環境だと帰ってきた配列をパスしないとだめ。
                     // ローカルだとそのまま使えた。
                     var parseAr = JSON.parse( arr );
                      $("p").text('検索結果：'+ parseAr.length+'件');
                  reWriteTable(parseAr);
               }
            });
         }
      );
   });
   // テーブルを書き換える関数
   function reWriteTable( response )
   {
      // 元にある行を削除
      $("#list tr").remove();
      // 取得したデータを行に入れる
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

