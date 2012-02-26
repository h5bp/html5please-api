      var $features = $('#features'),
         $options = $('#get-api').find('input'),
         $callback = '?callback=h5pCaniuse&',
         $h5pMessage = $('#h5p-message'),
         $widgetformat = $('input[name="widgetformat"]'),
         $apiresult = $('#api-result'),
         $body = $( document.body ),
         $lastscript = null,
         currentwidget = 0,
         cache = {},
         $widget = $('#widget'),
         $widgetmessage = $('#widget-message'),
         widget = {
           modernizr: 0,
           js: 1,
           uri: 2
         },
         jscontent = {
           prefix: '&lt;div id="h5p-message">&lt;/div>&lt;script>var x=document.getElementById("h5p-message");window.h5pCaniuse=function(a){x.innerHTML=a.html}&lt;/script>&lt;script src="',
           suffix: '&lt;/script>',
           message: 'For better performance, make sure you test for these features before invoking the widget'
         },

         modernizrcontent = {
           prefix: '&lt;div id="h5p-message">&lt;/div>&lt;script>Modernizr.browserPrompt=function(a,b){if(a.agents){Modernizr.browserPrompt.cb(a);return}var c=!0,d=a.features.split(" "),e=a.options,f;for(var g=-1,h=d.length;++g&lt;h;)f=d[g],!Modernizr[f]&&(c=!1);if(c)return c;var i=document.createElement("script"),j=document.getElementsByTagName("script")[0],k="http://api.html5please.com/"+d.join("+")+".json?callback=Modernizr.browserPrompt&html&"+e;return Modernizr.browserPrompt.cb=b,i.src=k,j.parentNode.insertBefore(i,j),!1},Modernizr.browserPrompt({',
           suffix: '},function(a){var b=document.getElementById("h5p-message");b.innerHTML=a.html})&lt;/script>',
           message: 'Make sure you include <a href="http://modernizr.com">modernizr</a> inside the head tag of your markup'
          };


     var api = {
          'domain': 'http://api.html5please.com/',
          'features': '',
          'format': '.json',
          'options': '',
          } 


      //autocomplete
      $(function() {
        var keywords = ['png-alpha', 'apng', 'video', 'audio', 'contenteditable', 'draganddrop', 'queryselector', 'getelementsbyclassname', 'forms', 'html5semantic', 'applicationcache', 'webworkers', 'fontface', 'eot', 'woff', 'multiplebgs', 'borderimage', 'background-img-opts', 'css-table', 'generatedcontent', 'css-fixed', 'hashchange', 'css-sel2', 'css-sel3', 'textshadow', 'boxshadow', 'css3-colors', 'css3-boxsizing', 'css-mediaqueries', 'csscolumns', 'borderradius', 'csstransforms', 'use-strict', 'csstransforms3d', 'sharedworkers', 'css-hyphens', 'css-transitions', 'font-feature', 'cssanimations', 'cssgradients', 'css-canvas', 'cssreflections', 'css-masks', 'svg', 'svg-css', 'smil', 'svg-fonts', 'svg-filters', 'svg-html', 'inlinesvg', 'canvas', 'canvastext', 'localstorage', 'websqldatabase', 'indexeddb', 'eventsource', 'x-doc-messaging', 'datauri', 'mathml', 'xhtml', 'xhtmlsmil', 'wai-aria', 'geolocation', 'flexbox', 'webgl', 'fileapi', 'websockets', 'script-async', 'cors', 'calc', 'ruby', 'opacity', 'form-validation', 'history', 'json', 'classlist', 'text-overflow', 'webm', 'mpeg4', 'ogv', 'wordwrap', 'progressmeter', 'object-fit', 'xhr2', 'minmaxwh', 'details', 'text-stroke', 'inline-block', 'notifications', 'stream', 'svg-img', 'datalist', 'dataset', 'css-grid', 'menu', 'rem', 'ttf', 'touch', 'matchesselector', 'pointer-events', 'blobbuilder', 'filereader', 'filesystem', 'bloburls', 'typedarrays', 'deviceorientation', 'script-defer', 'nav-timing', 'audio-api', 'css-regions', 'fullscreen', 'requestanimationframe', 'matchmedia'];

        function split( val ) {
          return val.split( /\s+/ );
        }
        function extractLast( term ) {
          return split( term ).pop();
        }
        function getUnusedKeywords( val ) {
          var terms = split( val );
          return $.grep( keywords, function( elem ) {
            return $.inArray( elem, terms ) === -1;
          });
        }

        $features
        .autocomplete({
          minLength: 0,
          source: function( request, response ) {
            response( $.ui.autocomplete.filter(
              getUnusedKeywords( this.element[0].value ), extractLast( request.term ) ) );
          },
          focus: function() {
            return false;
          },
          select: function( event, ui ) {
            var terms = split( this.value );
            // remove the current input
            terms.pop();
            // add the selected item
            terms.push( ui.item.value );
            terms.push(' ');
            this.value = terms.join(' ');
            api.features = this.value.trim().split(' ').join('+').trim();
            // Save the select state for use in the close event, which is called
            // after the menu is closed, and therefore can't be prevented.
            $apiresult.addClass('active');
            refreshOutput();
            return false;
          },
          close: function( event, ui ) {
          }
        })
      });

      $features.blur(function() {
         api.features = $features.attr('value').trim().split(' ').join('+').trim(); 
         if(api.features != '') {
           $apiresult.addClass('active');
         } else {
           $apiresult.removeClass('active');
         }
         refreshOutput();
      });

      $options.change(function() {
          refreshOutput();
      });

      $widgetformat.change(function() {
        currentwidget = this.value; 
        refreshOutput();
      });

      function formattedOptions() {
        var currentOptions = $options.filter(function(index) { return this.checked; });

        if(currentOptions.length > 0) {
          currentOptions = $.map(currentOptions, function(option) { 
            return option.value; 
          });
          return currentOptions.join('&');
        } else {
          return '';
        }
      } 

      function refreshOutput() {
         if(api.features !== '') {
           $script = $('<script>'),
           api.options = $callback + formattedOptions() + '&html';
           apiurl = createUrl(),
           $lastscript && $lastscript.remove();
           if(cache[apiurl]) {
             renderPreview(cache[apiurl], apiurl);
           } else {
             $body.append($script.attr('src', createUrl() + '&noagent'));
           }

           renderWidget(apiurl, currentwidget);
           $lastscript = $script;
         }
      };

      function createUrl() {
        return Object.keys(api).map(function(key) { 
          return api[key];
        }).join(''); 
      };

      function renderPreview(data, url) {
        if(data.supported){
          $h5pMessage.html('Your browser supports these features, which means the widget won’t render. Here is the <a target="_blank" href="' + url + '&readable">full JSON Object</a> that this widget uses.');
        } else {
          $h5pMessage.html(data.html);
        }
      };

      function renderWidget(url, type) {
        if(type == widget.modernizr) {
          $widget.html(modernizrcontent.prefix + 'features: "' + api.features + '", options:"' + formattedOptions() + '"' + modernizrcontent.suffix);
          $widgetmessage.html(modernizrcontent.message);
        } else if (type == widget.js) {
          $widget.html(jscontent.prefix + url + jscontent.suffix);
          $widgetmessage.html(jscontent.message);
        } else if (type == widget.uri){
          $widget.html('<a target="_blank" href="' + url + '&readable">'+ url + '</a>');
          $widgetmessage.html('');
        }
      };

      window.h5pCaniuse = function(data) {
        renderPreview(data);
        
        cache[createUrl()] = data;
      }

      refreshOutput();


      /* Smooth Scrolling */
      $toc = $('#toc'),
      $originalnavtop = $toc.position().top;
			$navheight = $toc.outerHeight(true);
			$('#nav_container').height($navheight),
      $stickynavheight = 0;

      $tocLinks = $toc.find('a[href^="#"]'),
			cache = {}, cacheinline = {};
			$docEl = $( document.documentElement ),
			$window = $( window ),
			$scrollable = $body
      
		if ( $docEl.scrollTop() ) {
			$scrollable = $docEl;
		} else {
			var bodyST = $body.scrollTop();
			if ( $body.scrollTop( bodyST + 1 ).scrollTop() == bodyST) {
				$scrollable = $docEl;
			} else {
				$body.scrollTop( bodyST - 1 );
			}
		}

		$tocLinks.each(function(i,v) {
			var href =  $( this ).attr( 'href' ),
				$target = $( href );
			if ( $target.length ) {
				cache[ this.href ] = { link: $(v), target: $target };
			}
		});


		$toc.delegate( 'a[href^="#"]', 'click', function(e) {
			e.preventDefault(); 
      if ( cache[ this.href ] && cache[ this.href ].target ) {
				$scrollable.animate( { scrollTop: cache[ this.href ].target.position().top - $stickynavheight }, 600, 'swing' );
			}
		});


		var deferred = false,
			timeout = false,
      last = false, 
      check = function() {
				var scroll = $scrollable.scrollTop();

				$.each( cache, function( i, v ) {
					if ( scroll + $stickynavheight >  (v.target.position().top - $stickynavheight)  ) {
						last && last.removeClass('active');
						last = v.link.addClass('active');
					} else {
						v.link.removeClass('active');
						return false; 					}
				});


				clearTimeout( timeout );
				deferred = false;
			};

		var $document = $(document).scroll( function() {

      if($scrollable.scrollTop() > ($originalnavtop)) {
        $toc.addClass('sticky').css('top', '0');
        $stickynavheight = ($(window).width() < 480) ? 0 : $toc.outerHeight();
      } else {
        $toc.removeClass('sticky');
      }

			if ( !deferred ) {
				timeout = setTimeout( check , 250 ); 
        deferred = true;
			}

			$oldscrolltop = $scrollable.scrollTop();

		});

		(function() {
			$document.scroll();
			setTimeout( arguments.callee, 1500 );
		})();

