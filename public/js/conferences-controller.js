
app.controller('conferencesController', function($scope, $http, $auth, $route) {

    function update(status, error) {
        $scope.status = status
        $scope.error = error
    }

	function updateConfs() {
		$http.get('/conferences').then(res => {
			$scope.conferences = res.data
		})
	}
	updateConfs()

	$scope.resetForm = () => {
		$scope.conference = {
			chairs: [
				{
					email: $auth.getUser().email,
					self: true
				},
				{}
            ]
		}
	}

	$scope.chairBlur = (chair) => {
		var chairs = $scope.conference.chairs
		var i = chairs.indexOf(chair)

		if ((!chair.email || !chair.email.length) && i != chairs.length-1) {
			chairs.splice(i, 1)
		}
	}
	$scope.chairChange = ($event) => {
		var chairs = $scope.conference.chairs
		var last = chairs[chairs.length-1]
		if (last.email && last.email.length) {
			$scope.addChair()
		} else {
			var slast = chairs[chairs.length-2]
			if (slast && (!slast.email || !slast.email.length)) {
				chairs.pop(chairs.length-1)
			}
		}
	}

	$scope.addChair = ($event) => {
		$scope.conference.chairs.push({})
	}

	$scope.gotoList = () => {
		$scope.conference = null
	}

	$scope.submit = () => {
		update('Saving...')
		$http.post('/conferences', $scope.conference).then(
			res => {
				$route.reload()
			},
			res => {
				update(null, res.data.error)
			}
		)
	}
})
