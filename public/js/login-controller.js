
app.controller('loginController', function($scope, $http, $auth) {
    function update(status, error) {
        $scope.status = status
        $scope.error = error
    }

    $scope.signup = () => {
        if (!$scope.email || !$scope.password) {
            update(null, 'Fields not valid.')
            return
        }

        var data = {
            email: $scope.email,
            password: $scope.password,
        }

        update('Signing up...', null)
        $http.post('/users', data).then(
            res => {
                $scope.signin()
            },
            res => {
                update(null, res.data.error)
            }
        )
    }

    $scope.signin = () => {
        if (!$scope.email || !$scope.password) {
            update(null, 'Fields not valid.')
            return
        }

        var data = {
            email: $scope.email,
            password: $scope.password,
        }

        update('Signing in...', null)
        $http.post('/sessions', data).then(
            res => {
                $auth.setToken(res.data.token)
                $auth.setUser(res.data.user)
                location.href = '#/'
            },
            res => {
                update(null, res.data.error)
            }
        )
    }
})