<?php

class Conversion extends AppModel
{
  var $name = "Conversion";

  /*
  //�@Get the record
  function paginate($conditions, $fields, $order, $limit, $page, $recursive, $extra = array())
  {
    //debug($conditions);

    if ($page == 0)
      $page = 1;

    $recursive = -1;
    $sql = "";
    //���R�[�h���擾����ׂ�SQL���쐬����֐����ق��ɂ���B
    $sql .= $this->make_sql($conditions);
    $sql .=' LIMIT ' . (($page - 1) * $limit) . ',' . $limit;
    return $this->query($sql);
  }

  //�AGet all count of data
  function paginateCount($conditions = null, $recursive = 0, $extra = array())
  {
    $this->recursive = $recursive;

    //���R�[�h���擾����ׂ�SQL���쐬����֐����ق��ɂ���B
    $results = $this->query($this->make_sql($conditions));
    return count($results);
  }

  function make_sql ($conditions = null)
  {
    $query = "SELECT COUNT(*) AS count, appsigid, LEFT(created, 10) AS created FROM conversions ";
    $count = 0;
    foreach ($conditions as $c => $rec)
    {
      if ($count == 0)
        $query .= "WHERE ". $c . " = '" . $rec. "'";
      else
        $query .= " AND ". $c . " = " . $rec;

      $count++;
    }
    $query .= " GROUP BY LEFT(created, 10) ORDER BY LEFT(created, 10) DESC";
    //debug($query);
    return $query;
  }
  */
}
