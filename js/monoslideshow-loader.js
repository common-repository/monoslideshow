(function(win, doc) {

	win['MonoslideshowLoader'] = function() {

		var _monoslideshows = [];
		var _isLoaded = false;

		function getValue(value, fractionValue) {
			if (fractionValue) {
				return Math.round(Number(value) * Number(fractionValue.replace(',', '.'))) + 'px';
			}
			if (!value) {
				return '';
			}
			if ((/[0-9]+\s?%$/).test(value)) {
				return value;
			}
			if ((/[0-9]+\s?px$/i).test(value)) {
				return value;
			}
			return Math.round(value) + 'px';
		}

		function isFraction(value) {
			return (/[0-9]*[\.\,][0-9]*$/).test(value);
		}

		function onWindowResize() {
			for (var i = 0, l = _monoslideshows.length; i < l; i++) {
				var mss = _monoslideshows[i];
				if (mss.resize) {
					resizeMonoslideshow(mss);
				}
			}
		}

		function resizeMonoslideshow(mss) {
			var parent = doc.getElementById('monoslideshow' + mss.id).parentNode;
			if (isFraction(mss.w)) {
				parent.style.height = getValue(mss.h);
				parent.style.width = getValue(parseInt(parent.offsetHeight, 10), mss.w);
			} else if (isFraction(mss.h)) {
				parent.style.width = getValue(mss.w);
				parent.style.height = getValue(parseInt(parent.offsetWidth, 10), mss.h);
			} else {
				if (mss.w) {
					parent.style.width = getValue(mss.w);
				}
				if (mss.h) {
					parent.style.height = getValue(mss.h);
				}
			}
			mss.monoslideshow.resize(parent.offsetWidth || 16, parent.offsetHeight || 16);
			mss.resized = true;
		}

		function getInitializedFunction(mss) {
			return function() {
				mss.initialized = true;
				if (_isLoaded && mss.resize) {
					resizeMonoslideshow(mss);
				}
			};
		}

		function add(id, w, h, xml, resize) {
			var monoslideshow = new Monoslideshow('monoslideshow' + id, {'showLogo': false});
			var mss = {monoslideshow: monoslideshow, id: id, w: w, h: h, resize: resize, resized: false, initialized: false};
			_monoslideshows.push(mss);
			monoslideshow.addEventListener('monoslideshowInitialized', getInitializedFunction(mss));
			monoslideshow.loadXMLString(xml);
		}

		function getMonoslideshowByID(id) {
			for (var i = 0, l = _monoslideshows.length; i < l; i++) {
				if (_monoslideshows[i].id == id) {
					return _monoslideshows[i];
				}
			}
			return null;
		}

		function getMonoslideshowByHolder(holder) {
			for (var i = 0, l = _monoslideshows.length; i < l; i++) {
				var parent = doc.getElementById('monoslideshow' + _monoslideshows[i].id).parentNode;
				if (parent == holder) {
					return _monoslideshows[i];
				}
			}
			return null;
		}

		function onWindowLoad() {
			_isLoaded = true;
			for (var i = 0, l = _monoslideshows.length; i < l; i++) {
				if (!_monoslideshows[i].resized && _monoslideshows[i].initialized) {
					resizeMonoslideshow(_monoslideshows[i]);
				}
			}
		}

		win.addEventListener('resize', onWindowResize);
		win.addEventListener('load', onWindowLoad);

		return {
			add: add,
			getMonoslideshowByID: getMonoslideshowByID,
			getMonoslideshowByHolder: getMonoslideshowByHolder
		};

	}();

})(window, window.document);