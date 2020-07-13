document.addEventListener("DOMContentLoaded", function () {
	var loader_img='https://buylocksmithdeals.com/wp-content/plugins/buylocksmithdeals-customization-addon/assets/img/loader.gif';
	var loader_style="position: fixed;left: 0;right: 0;width: 100%;height: 100%;background-color: hsla(0, 0%, 100%, 0.80);z-index: 9999999 !important;top: 0;bottom: 0;align-items: center;justify-content: center;overflow: hidden;display: none;";
	var loader_style_img="max-width: 60px;";
		document.body.innerHTML += '<div id="loader" style="'+loader_style+'"><img style="'+loader_style_img+'" src="'+loader_img+'"></div>';
            var product_div = document.getElementsByClassName('blsd_pro_list');
			var loader=document.getElementById('loader');
			
            for (var i = 0; i < product_div.length; i++) {
				if(i==0){
					if (loader.style.display == "none") {
						loader.style.display = "flex";
					}
				}
				
				
                var pro_div = document.getElementsByClassName("blsd_pro_list")[i];
				if (pro_div.getAttribute("id") === null) {
                    var id = '';
                }
                else {
                    var id = pro_div.getAttribute("id");
                }
				if (pro_div.getAttribute("api_key") == null) {
                    var api_key = '';
                }
                else {
                    var api_key = pro_div.getAttribute("api_key");
                }
				if (pro_div.getAttribute("layout") === null) {
                   var layout = '';
                }
                else {
                    var layout = pro_div.getAttribute("layout");
                }
				
				
				
				
               
                if (pro_div.getAttribute("sort") === null) {
                    var sort = '';
                }
                else {
                    var sort = pro_div.getAttribute("sort");
                }
                if (pro_div.getAttribute("records") === null) {
                    var records = '';
                }
                else {
                    var records = pro_div.getAttribute("records");
                }
                if (pro_div.getAttribute("deal") === null) {
                    var deal = '';
                }
                else {
                    var deal = pro_div.getAttribute("deal");
                }
				if (pro_div.getAttribute("category") === null) {
                    var category = '';
                }
                else {
                    var category = pro_div.getAttribute("category");
                }
                if (pro_div.getAttribute("see_more_deals") === null) {
                    var see_more_deals = '';
                }
                else {
                    var see_more_deals = pro_div.getAttribute("see_more_deals");
                }
                if (pro_div.getAttribute("vendor_url") === null) {
                    var vendor_url = '';
                }
                else {
                    var vendor_url = pro_div.getAttribute("vendor_url");
                }

                var api_url = pro_div.getAttribute("api_url");
                //alert(api_url);

                var url = api_url+'get_products';
                var http = new XMLHttpRequest();

                var params = 'api_details=' + api_key + '&layout=' + layout + '&sort=' + sort + '&records=' + records + '&deal=' + deal + '&category='+category + '&see_more_deals=' + see_more_deals + '&vendor_url=' + vendor_url +'&id='+id;

                //  get_response(url,params,div_id);

				var length=product_div.length;
				makeRequest('post', url, params, pro_div, i, length)
                    .then(function (response) { })
                    .catch(function (err) { });
					
				
            }
			
			
        });

        function makeRequest(method, url, params, pro_div, init, length) {
            return new Promise(function (resolve, reject) {
                var xhr = new XMLHttpRequest();
                xhr.open(method, url);
                xhr.onload = function () {
                    if (this.status >= 200 && this.status < 300) {
                        var dataFound = JSON.parse(xhr.response);
                        pro_div.innerHTML = dataFound.html;
                        resolve(xhr.response);
						
                    } else {
                        reject({
                            status: this.status,
                            statusText: xhr.statusText
                        });
                    }
					if(init == length-1){
						loader.style.display = "none";
					}
					
					
                };
                xhr.onerror = function () {
                    reject({
                        status: this.status,
                        statusText: xhr.statusText
                    });
                };
                xhr.send(params);
				
            });
        }

