    <?php
        //print $this->Paginator->counter('{:count}����{:start}-{:end}��({:pages}�y�[�W��{:page}�y�[�W)');
        if($this->Paginator->hasPrev()) print $this->Paginator->prev('��' , array('class'=>'block'));
        print $this->Paginator->numbers(array(
        'class'=>'block',
        'modules' => 6 ,
        'first'=>2,
        'last'=>2,
        'currentClass'=>'red',
        'separator'=>null
        ));
        if($this->Paginator->hasNext()) print $this->Paginator->next(' ��' , array('class'=>'block'));
    ?>
