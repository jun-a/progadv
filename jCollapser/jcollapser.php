<?php echo '<?xml version="1.0" encoding="UTF-8" ?>'; ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>jCollapser Demo page</title>
<script type="text/javascript" src="jquery-1.3.2.min.js"></script>
<script type="text/javascript" src="jquery.jcollapser-1.2.1.js"></script>
<script type="text/javascript" src="jquery.cookie.js"></script>
<script type="text/javascript">
$(function() {
<?php
    /* Create initialize multiple elements. */
    $state = 'expanded';    

    for($i = 1; $i <= 5; $i++) {
        $state = $state == 'expanded' ? 'collapsed' : 'expanded';
        ?>
        $("#<?php echo $i; ?>").jcollapser({target: '#t_<?php echo $i; ?>', state: '<?php echo $state; ?>'});
        <?php
    }
?>
});
</script>
<style type="text/css">
.collapse {
    background: transparent url('./collapse.gif') no-repeat scroll 0% 0%;
    height: 11px;
    position: relative;
    
    width: 11px;
}

.expand {
    background:transparent url('./expand.gif') no-repeat scroll 0% 0%;
    display:none;
    height:11px;
    position:relative;
    
    width:11px;
}
</style>
</head>
<body>
<?php
    for($i = 1; $i <= 5; $i++) {
        ?>
<div>
    <fieldset id="<?php echo $i; ?>">
    <div class="collapse" ></div>
    <div class="expand" ></div>
    <div id="t_<?php echo $i; ?>">
    Lorem ipsum dolor sit amet, consectetur adipiscing elit. Etiam arcu ante, pellentesque quis, rhoncus et, molestie in, lacus. Phasellus id purus id enim malesuada dictum. Maecenas tellus magna, tristique at, ultrices mollis, rhoncus id, sapien. Sed volutpat est ut magna. Nulla facilisi. Curabitur lacus diam, semper vitae, convallis sodales, adipiscing convallis, leo. Nulla dictum dignissim ligula. In posuere. In eleifend elit eget sapien. Suspendisse eget eros eget pede facilisis aliquam. Etiam rhoncus. Nullam quis tellus eget orci placerat aliquet.
    <div>
    </fieldset> 
</div>
        <?php
    }
?>
</body>
</html>