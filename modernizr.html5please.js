// HTML5 Please API, Modernizr plugin
// v0.5


/*
  usage:


Modernizr.html5please({
    features : 'opacity fontface postmessage regions',     
    yep      : function(){ // tests all pass
        initApp();
    },
    nope : function(data){ // 1+ test fails. passed data payload from api
        console.log('hi',data);
    }
}); 

*/


Modernizr.html5please = function(opts){
    
    var passes = true;
    var features = opts.features.split(' ');
    var feat;
    for (var i = -1, len = features.length; ++i < len; ){
        feat = features[i];
        if (Modernizr[feat] === undefined) window.console && console.warn('Modernizr.' + feat + ' test not found');
        if (Modernizr[feat] === false) passes = false;
    }
    // your browser is good enough!
    if (passes){
        opts.yep && opts.yep();
        return passes;  
    } 

    // if we got here, we're not tall enough to ride.

    // cache callback
    Modernizr.html5please.cb = opts.nope;

    // make API call
    var script = document.createElement('script');
    var ref = document.getElementsByTagName('script')[0];
    var url = 'http://api.html5please.com/' + features.join('+') + '.html?callback=Modernizr.html5please.cb';
    script.src = url;
    ref.parentNode.insertBefore(script, ref);

    // your browser failed 
    return false;

};