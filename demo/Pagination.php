<?php

function PaginationRequest($tbl, $where, $group, $join = '', $select = '*'){
	/* get low; get high */
	$lower = $_GET["lw"];
	$upper = $_GET["hg"];
	
	/* Join and select fields to allow for more complex queries if required */
	$sql = "SELECT $select FROM $tbl
					WHERE $where
					$join
					GROUP BY $group DESC
					LIMIT $lower, $upper";
	
	/* I would like to set the new  */
	return $result = mysqli_query($this->con, $sql);	
}
?>
