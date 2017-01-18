
app.controller('articleFormController', function($scope, $http, $route) {
	function update(status, error) {
		$scope.status = status
		$scope.error = error
	}

	$scope.submit = () => {
		update('Saving...')
		$http.post('/articles', $scope.article).then(
			res => {
				$route.reload()
			},
			res => {
				update(null, res.data.error)
			}
		)
	}
})

