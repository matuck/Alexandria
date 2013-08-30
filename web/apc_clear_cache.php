<?php
echo apc_clear_cache();
echo apc_clear_cache('user');
echo apc_clear_cache('filehits');
echo '<pre>';
print_r(apc_cache_info());
print_r(apc_cache_info('user'));
print_r(apc_cache_info('filehits'));
echo '</pre>';
?>

