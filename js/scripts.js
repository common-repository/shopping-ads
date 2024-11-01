function shopping_ADS_copy_key(id) {
	//Deprecated

	document.getElementById(id).select();
	document.getElementById(id).setSelectionRange(0, 99999);
	document.execCommand("copy");
}

const $ = jQuery
// Api Rest Keys:
$(document).ready(function() {
	var consumer_key = $('#key_consumer_key').val();
	var consumer_secret = $('#key_consumer_secret').val();

	// Crypto token
	var keyEncrypted = encodeURIComponent(CryptoJS.AES.encrypt(consumer_key, 'q63FH&Zxk~J98?'));
	var secretEncrypted = encodeURIComponent(CryptoJS.AES.encrypt(consumer_secret, 'q63FH&Zxk~J98?'));

	var url = encodeURIComponent(window.location.origin)

	//Link with encrypted keys, this receive Shopping Ads
	var link = $('#link-button').attr('href')
	$('#link-button').attr('href', link+'?key='+keyEncrypted+'&secret='+secretEncrypted+'&url='+url);



	//var bytes = CryptoJS.AES.decrypt(encrypted.toString(), 'otro')
})