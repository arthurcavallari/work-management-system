<?php
 
class pageNavigation {
 
    var $noOfPages;
    var $currPage;
    var $previousStartItem;
    var $nextStartItem;
     
    var $pages;
     
    // constructor
    function pageNavigation($noOfItems, $itemsPerPage, $startItem) {
     
        if ($itemsPerPage <= 1) $itemsPerPage = 2;
        elseif ($itemsPerPage > $noOfItems && $noOfItems != 0) $itemsPerPage = $noOfItems;
         
        if ($startItem < 0) $startItem = 0;
 
        $this->noOfPages = ceil($noOfItems / $itemsPerPage);
                 
        $this->currPage = ceil( ($startItem+1) / $itemsPerPage);
         
        if($this->currPage > $this->noOfPages) { $this->currPage = $this->noOfPages; }
         
        $this->previousStartItem = ($this->currPage-2)*$itemsPerPage;
        if($this->previousStartItem < 0) $this->previousStartItem = -1;
         
        $this->nextStartItem = $this->currPage*$itemsPerPage;
        if($this->nextStartItem > $noOfItems-1) $this->nextStartItem = -1;
                 
        // create pages
        $tempArray = array();
        // first part
        for($i = 1; $i <= 3; $i++) {
            if($i >= 1 && $i <= $this->noOfPages) { $tempArray[] = $i; }          
        }
        // middle part
        for($i = $this->currPage-6; $i <= $this->currPage+6; $i++) {
            if($i >= 1 && $i <= $this->noOfPages) { $tempArray[] = $i; }          
        }
        // last part
        for($i = $this->noOfPages-2; $i <= $this->noOfPages; $i++) {
            if($i >= 1 && $i <= $this->noOfPages) { $tempArray[] = $i; }          
        }
        $tempArray = array_unique($tempArray); //cut off duplicate entries
        sort($tempArray);
         
        // and create the array containing the pages
        $this->pages = array();
        $tempLast = -1;
        foreach($tempArray as $key => $value) {
            if($key != 0 && $value > ($tempLast + 1)) { // put an empty interval if there's a jump
                $this->pages[] = array("pageno" => -1, "startitem" => -1);
            }
            $this->pages[] = array("pageno" => $value, "startitem" => (($value-1)*$itemsPerPage));
            $tempLast = $value;
        }
         
         
    }
     
}
 
?>