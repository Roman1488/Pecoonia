<script type="text/javascript">
if (window.opener != null && !window.opener.closed)
{
   window.opener.socialLoginCallback(<?php echo json_encode($response); ?>);
   window.close();
}
</script>