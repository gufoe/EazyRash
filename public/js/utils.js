Array.prototype.diff = function(a) {
	return this.filter(function(i) { return a.indexOf(i) < 0; })
}

Array.prototype.byField = function(field, value) {
	return this.filter(function ( obj ) {
		return obj[field] == value;
	})[0]
}

Array.prototype.each = function(cb) {
	for (var i = 0; i < this.length; i++) {
		cb(this[i])
	}
}

Array.prototype.lists = function(field) {
	var l = []
	this.each(e => {
		l.push(e[field])
	})
	return l
}

function copyTo(src, dst) {
	for (var i in src)
		dst[i] = src[i]
}