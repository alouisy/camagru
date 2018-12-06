(function() {
	// The width and height of the captured photo. We will set the
	// width to the value defined here, but the height will be
	// calculated based on the aspect ratio of the input stream.

	var width = 640;    // We will scale the photo width to this
	var height = 0;     // This will be computed based on the input stream
	
	// |streaming| indicates whether or not we're currently streaming
	// video from the camera. Obviously, we start at false.
	
	var streaming = false;
	
	// The various HTML elements we need to configure or control. These
	// will be set by the startup() function.
	
	var video = null;
	var canvas = null;
	var canvas2 = null;
	var photo = null;
	var startbutton = null;
	var constraints = { video: { frameRate: { ideal: 10, max: 15 } } };

	function startup() {
		video = document.getElementById('video');
		canvas = document.getElementById('canvas');
		canvas2 = document.getElementById('canvas2');
		photo = document.getElementById('photo');
		startbutton = document.getElementById('startbutton');
		
		// Older browsers might not implement mediaDevices at all, so we set an empty object first
		if (navigator.mediaDevices === undefined) {
		  navigator.mediaDevices = {};
		}
		
		// Some browsers partially implement mediaDevices. We can't just assign an object
		// with getUserMedia as it would overwrite existing properties.
		// Here, we will just add the getUserMedia property if it's missing.
		if (navigator.mediaDevices.getUserMedia === undefined) {
		  navigator.mediaDevices.getUserMedia = function(constraints) {
		
		    // First get ahold of the legacy getUserMedia, if present
		    var getUserMedia = navigator.webkitGetUserMedia || navigator.mozGetUserMedia;
		
		    // Some browsers just don't implement it - return a rejected promise with an error
		    // to keep a consistent interface
		    if (!getUserMedia) {
		      return Promise.reject(new Error('getUserMedia is not implemented in this browser'));
		    }
		
		    // Otherwise, wrap the call to the old navigator.getUserMedia with a Promise
		    return new Promise(function(resolve, reject) {
		      getUserMedia.call(navigator, constraints, resolve, reject);
		    });
		  }
		}
		
		navigator.mediaDevices.getUserMedia({ audio: false, video: true })
		.then(function(stream) {
		  var video = document.querySelector('video');
		  // Older browsers may not have srcObject
		  if ("srcObject" in video) {
		    video.srcObject = stream;
		  } else {
		    // Avoid using this in new browsers, as it is going away.
		    video.src = window.URL.createObjectURL(stream);
		  }
		  video.onloadedmetadata = function(e) {
		    video.play();
		  };
		})
		.catch(function(err) {
		  console.log(err.name + ": " + err.message);
		});
	
		video.addEventListener('canplay', function(ev){
			if (!streaming) {
				height = video.videoHeight / (video.videoWidth/width);
				
				// Firefox currently has a bug where the height can't be read from
				// the video, so we will make assumptions if this happens.
				
				if (isNaN(height)) {
					height = width / (4/3);
				}
				
				video.setAttribute('width', width);
				video.setAttribute('height', height);
				canvas.setAttribute('width', width);
				canvas.setAttribute('height', height);
				streaming = true;
			}
		}, false);
		
		startbutton.addEventListener('click', function(ev){
			takepicture();
			ev.preventDefault();
		}, false);
		
		//clearphoto();
	}
	
	// Fill the photo with an indication that none has been
	// captured.
	
	// Capture a photo by fetching the current contents of the video
	// and drawing it into a canvas, then converting that to a PNG
	// format data URL. By drawing it on an offscreen canvas and then
	// drawing that to the screen, we can change its size and/or apply
	// other changes before drawing it.
	
	function takepicture() {
		var context = canvas.getContext('2d');
		var context2 = canvas2.getContext('2d');
		
		if (width && height) {
			canvas.width = width;
			canvas.height = height;
			context.translate(width, 0);
			context.scale(-1, 1);
			context.drawImage(video, 0, 0, width, height);

			var data = canvas.toDataURL('image/png');
			document.getElementById('my_hidden').value = data;
			
			canvas2.width = width;
			canvas2.height = height;
			context2.translate(width, 0);
			context2.scale(-1, 1);
			context2.drawImage(document.getElementById('filtre'), 0, 0, width, height);
			context.drawImage(canvas2, 0, 0, width, height);
						
			data = canvas.toDataURL('image/png');
			photo.setAttribute('src', data);

		} else {
			clearphoto();
		}
	}
		
	// Set up our event listener to run the startup process
	// once loading is complete.
	window.addEventListener('load', startup, false);
})();