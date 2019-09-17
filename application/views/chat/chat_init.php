<script>
	var base_url = "<?php print base_url(); ?>";
	var socket = io.connect('<?php echo  $this->config->item('OMNI_WEBHOOK_SOCKET_URL')?>', {
	    reconnection: true,
	    reconnectionDelay: 500,
	    reconnectionDelayMax : 5000,
	    reconnectionAttempts: Infinity
	    /*rememberTransport: false,
	    transports: ['WebSocket', 'Flash Socket', 'AJAX long-polling']*/
	});
	/*io.connect('http://localhost:843',{
	    rememberTransport: false,
	    transports: ['WebSocket', 'Flash Socket', 'AJAX long-polling']
	});*/
	Vue.config.productionTip = false
</script>