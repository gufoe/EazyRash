
app.controller('loginController', function($scope, $http, $auth, $location) {
    $scope.sign_up = false
    $scope.form = {}

    $status = {
        reset: () => {
            $scope.info = null
            $scope.error = null
            $scope.success = null
        },
        info: (msg) => { $status.reset(); $scope.info = msg},
        error: (msg) => { $status.reset(); $scope.error = msg},
        success: (msg) => { $status.reset(); $scope.success = msg},
    }

    $scope.signup = () => {
        if (!$scope.form.email || !$scope.form.password || !$scope.form.name) {
            $status.error('Campi non validi.')
            return
        }

        $status.info('Signing up...', null)
        $http.post('/users', $scope.form).then(
            res => {
                console.log('signup')
                $scope.signin()
            },
            res => {
                console.log('error signup')
                $status.error(res.data.error)
            }
        )
    }

    $scope.signin = () => {
        if (!$scope.form.email || !$scope.form.password) {
            $status.error('Campi non validi.')
            return
        }

        $status.info('Signing in...', null)
        $http.post('/sessions', $scope.form).then(res => {
            $auth.setToken(res.data.token)
            $auth.setUser(res.data.user)
            $status.success('Logged in!')
            $location.path('/')
        })
    }

})
