# HTML5 Please API

## Introduction

Developers wishing to use modern browser features in their applications or demos often run into a very common issue: a user somewhere may be using a browser **incompatible** with what your project needs to function correctly. There are instances where this behaviour can be normalized using polyfills, but for all other instances, it's necessary to display a notification or message informing the user they need to **upgrade** to a different browser. The HTML5 Please API can help with this.

Rather than needing to take care of browser detection and manually listing the latest versions of browsers supported each time you use a modern feature, we provide a solution for this. Our **API** uses data sourced from [caniuse](http://caniuse.com)
and retrieves browser support information based on features you require on the client-side.  For example, if you want your page to only render on browsers that support Transitions and Canvas, you can send in an API request to [api.html5please.com](http://api.html5please.com) and print the returned HTML as a message when the page is viewed on browsers that do not support these features. 

We also provide a number of useful snippets to save you time creating a notification for your users. The project can generate widgets using a special [Modernizr](http://modernizr.com) plugin, pure JavaScript snippets that do the same or simply HTML links for those that wish to link their users out to a page with suggestions on browsers to consider upgrading to in order to support all the features needed for your site. 

# Reference

Most users will be able to configure a snippet for their needs using our main widget/snippet [builder](http://api.html5please.com). If however you would like to learn more about the options supported by the API, please see below for more details.

## API URIs

URI Format: 

http://api.html5please.com/[feature1+feature2..+featureN].[format]?[option1&option2..optionN]

URI Example:

http://api.html5please.com/video+audio+webgl.json?callback=h5please&texticon&html

For more information on formats supported, see the 'Formats' section below.


## Features

We support the majority of HTML5 features and probably cover any feature you're looking for. Please feel free to look at the `keywords.json` to find out what features are supported. Alternatively you can use the autocomplete on the site to do so.


## Formats

Data from the API is output either as a JSON object, XML or HTML (depending on the format you choose). For complete details on our data objects, see the [wiki](https://github.com/h5bp/html5please-api/wiki/Data-Object-Reference).

- `html`: the output will be valid HTML with the mimetype of `text/html`. 

- `json`: the output will be valid JSON with the mimetype of `text/json`. 

- `xml`: the output will be valid XML with the mimetype of `text/xml`.

## Options

- `nocss`: the HTML will not include the stylesheet. 

- `text`: the HTML will be optimized for text output. 

- `icon`: the HTML will be optimized for icon output. 

- `supported`: the JSON will only output whether the agent supports the requested features. 

- `noagent`: the JS will return results for all browsers with no agent detection. 

- `callback = [ functionName ]`: the output will be JavaScript, wrapped in this function name.

## UA Detection

The HTML5 Please service uses UA detection as a part of our process for detecting the browser currently used by the user. This is strictly used for deciding what browsers and browser versions we should recommend end-users upgrade to in order to use a feature. For more information on this, please see [this](https://github.com/h5bp/html5please-api/wiki/How-does-the-UA-Detection-work%3F
) entry on the wiki.

## Examples

We will be posting more demos and examples of the how the project can be used shortly. In the mean time if you would like to look at two projects already making use of the API, see [http://mothereffinganimatedgif.com/](http://mothereffinganimatedgif.com/) or [http://lab.simurai.com/flashlight/](http://lab.simurai.com/flashlight/).

# History

Divya has a [post](http://nimbupani.com/html5please-api.html) about the project history we recommend checking out. This project began as one of her [LazyWeb](https://github.com/paulirish/lazyweb-requests/issues/39) requests for a healthy choose-your-browser page. [@addyosmani](http://github.com/addyosmani) worked on the initial implementation, to be later replaced with a much more rich API and UI by [@jonathantneal](http://github.com/jonathantneal), [@nimbupani](http://github.com/nimbupani) and other contributors. Jons service is what currently powers much of the HTML5 Please API. We are still very much working on improving it and recommend looking at the 'Contributing' section below in case you would like to help out.

# Contributing

We welcome any and all contributions to improve the project. Please feel free to fork and send through a pull request if there's something you feel we could be doing better.

Note that the site that represents this project runs off `index.html` and references files from `site` folder. It uses [SASS](http://sass-lang.com/) so please make any changes to `style.scss` rather than `style.css`. The logic for much of the UI is based in `site/js/script.js` should any changes need to be made on that front.

# Credits

- @fyrd for amazing caniuse API which underlies this work
- @jonathantneal for all the magic with PHP and building a robust API on top of caniuse's data
- @aaronlayton for initial design of html output
- @drublic for various fixes to the builder/UI
- @addyosmani for help with the docs
- @divya & @paul_irish for corralling the project together



