
app.controller('articleController', function($scope, $http, $route, $up) {
	$scope.uploadCommit = () => {
		var article = $scope.article
		var input = $('<input type=file>')
		input.change(() => {
			var files = input[0].files
			if (!files.length) return
			var data = new FormData()
			data.append('file', files[0])
			data.append('article_id', article.id)
			article.uploading = true
			$http.post('/commits', data, {
				headers: {'Content-Type': undefined}
			}).then(
				res => {
					article.uploading = false
					$scope.refresh()
				},
				res => {
					article.uploading = false
					alert(res.data.error)
				}
			)
		})
		input.click()
	}

	$scope.selectReviewers = () => {
		$up.show(users => {
			var data = {
				users: users.lists('id')
			}
			$http.post('/articles/'+$scope.article.id+'/reviewers/', data).then(res => {
				copyTo(res.data, $scope.article)
			})
		}, $scope.article.reviewers)
	}
})