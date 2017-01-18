app.config(function($routeProvider) {
	$routeProvider
		.when('/', {
			templateUrl: '/pages/home.html',
			controller: 'homeController'
		})
		// Conference list, new/edit conference
		.when('/conferences/', {
			templateUrl: '/pages/conferences.html',
			controller: 'conferencesController'
		})
		// Conference details
		.when('/conferences/:id/', {
			templateUrl: '/pages/conference.html',
			controller: 'conferenceController'
		})
		// Article editor
		.when('/articles/:id/', {
			templateUrl: '/pages/article.html',
			controller: 'editorController'
		})
		// Login/signup
		.when('/login', {
			templateUrl: '/pages/login.html',
			controller: 'loginController'
		})
})

app.factory('AuthInterceptor', function($rootScope, $q, AUTH_EVENTS) {
	return {
		responseError: (response) => {
			$rootScope.$broadcast({
				401: AUTH_EVENTS.notAuthenticated,
				403: AUTH_EVENTS.notAuthorized
			}[response.status], response)
			return $q.reject(response)
		}
	}
})

app.config(function($httpProvider) {
	$httpProvider.interceptors.push('AuthInterceptor')
})

app.filter('datify', function() {
    return function(date) {
        return new Date(date)
    }
})

app.controller('mainController', function($scope, $http, $auth, $location, AUTH_EVENTS) {
	$scope.logged = $auth.logged
	$scope.logout = $auth.logout
	$scope.message = 'mainc'
	$scope.user = () => { return $auth.getUser() }
	$scope.$on(AUTH_EVENTS.notAuthenticated, function(event) {
		$auth.setToken(null)
		location.href = '#/login'
	})

	$scope.isMenu = path => {
		if ($location.path() == path)
			return true
		return false
	}
})

app.controller('homeController', function($scope, $http, $auth) {
	$http.get('/users/self').then(res => {
		$scope.self = res.data
	})
	$http.get('/conferences/').then(res => {
		$scope.conferences = res.data.reverse()
	})
	$http.get('/articles/').then(res => {
		$scope.articles = res.data.reverse()
	})
})

app.controller('editorController', function($scope, $http, $routeParams, $sce, $interval) {

	var $editor = $('#editor')
	var $rash = null
	var id = $routeParams.id
	$scope.editing = false

	var edits = null

	var addComment = (id, content) => {
		edits.comments.added.push({
			target: '#'+id,
			content: content,
		})
		$scope.$apply()
	}

	var escapeRash = (r) => {
		r = r.replace(/html>/g, 'xhtml>')
		r = r.replace(/head>/g, 'xhead>')
		r = r.replace(/body>/g, 'xbody>')
		return r
	}
	var unscapeRash = (r) => {
		r = r.replace(/xhtml>/g, 'html>')
		r = r.replace(/xhead>/g, 'head>')
		r = r.replace(/xbody>/g, 'body>')
		return r
	}

	$scope.save = () => {
		$rash.find('xbody').html($editor.html())
		var r = unscapeRash($rash.html())

		$http.post('/commits/'+$scope.article.commit.id+'/review', {rash: r, edits: edits}).then(res => {
			$scope.stopEditing()
		}, res => {
			alert('Errore: '+res.data.error)
		})
	}

	$scope.startEditing = () => {
		$http.post('/articles/' + id + '/lock').then(res => {
			if (res.data.locked) {
				if (!$scope.editing) {
					$scope.editing = true
					edits = $scope.edits = {
						comments: {
							added: [],
							removed: []
						},
						review: $scope.article.commit.review
					}
				}
			} else {
				$scope.editing = false
				alert('The article is being reviewed by somebody else now, try again later.')
			}
		})
	}

	$scope.stopEditing = () => {
		$http.post('/articles/' + id + '/unlock').then(res => {
			$scope.update()
			$scope.editing = false
		})
	}

	$scope.init = () => {
		$scope.stopEditing()

		$scope.update()
	}

	$scope.update = (callback) => {
		$http.get('/articles/' + id).then(res => {
			var article = $scope.article = res.data

			// $rash represents the rash xml structure
			$rash = $('<div/>').html(escapeRash(article.commit.rash))

			// Insert the rash body in the editor
			$editor.html($rash.find('xbody').html())

			// Load comments
			$.each(article.commit.comments, (i, comment) => {
				$(comment.target).click(function() {
					$scope.comment = comment
					if ($scope.editing) return

					BootstrapDialog.show({
						title: $('<div>Comment from </div>').append(comment.user.email),
			            message: $('<div/>').html(comment.content)
			        });

				})
			})

		    $scope.$on('$destroy', function() { $interval.cancel(int) })

			$editor.mouseup(() => {
				if (!$scope.editing)
					return

				// Create a span element for the comment
				var span = document.createElement('span');
				span.classList.add('comment')
				span.id = 'comment-'+parseInt(Math.random()*1000000)

		        var sel = window.getSelection();
		        if (sel.rangeCount) {
					var range = sel.getRangeAt(0).cloneRange();
					if (range.endOffset-range.startOffset == 0) {
						// Nothing selected
						return
					} else if (range.startContainer.parentElement!=range.endContainer.parentElement) {
						// Invalid range
						alert('Invalid selection')
					} else if ((content = prompt('Comment'))) {
						// Save the annotation
						range.surroundContents(span)
						sel.removeAllRanges()
						sel.addRange(range)
						setTimeout(() => {addComment(span.id, content)}, 1000)
					}
					// Clear selection
					window.getSelection().empty()
		        }
			})

			callback && callback()
		})
	}


	// Setup autorefresh interval
	var int = $interval(() => {
		if ($scope.editing) {
			$scope.startEditing()
		}
	}, 5000)
})

app.controller('sideArticlesController', function($scope, $http, $auth) {
    $http.get('/articles/').then(res => {
        $scope.articles = res.data.reverse()
    })
})
app.controller('sideConferencesController', function($scope, $http, $auth) {
    $http.get('/conferences/').then(res => {
        $scope.conferences = res.data.reverse()
    })
})
