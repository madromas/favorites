<?php

    $this->addBreadcrumb(LANG_PERMISSIONS);
	
?>

<?php

    $submit_url = $this->href_to('perms_save', $subject ? $subject : false);

    echo $this->renderPermissionsGrid($rules, $groups, $values, $submit_url);

?>
