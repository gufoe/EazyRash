
app.controller('conferenceController', function($scope, $http, $routeParams, $up) {
	var id = $routeParams.id
	$scope.form = null

	$scope.refresh = () => {
		$http.get('conferences/'+id).then(res => {
			$scope.conference = res.data
		})
	}

	$scope.setArticleForm = () => {
		$scope.form = 'article'
		$scope.article = {
			conference_id: $scope.conference.id
		}
	}

	$scope.gotoList = () => {
		$scope.form = null
	}


	$scope.selectChairs = () => {
		$up.show(users => {
			var data = {
				users: users.lists('id')
			}
			$http.post('/conferences/'+$scope.conference.id+'/chairs/', data).then(res => {
				copyTo(res.data, $scope.conference)
			})
		}, $scope.conference.chairs)
	}
})
