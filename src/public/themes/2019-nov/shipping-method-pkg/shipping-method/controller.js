app.config(['$routeProvider', function($routeProvider) {

    $routeProvider.
    when('/tags', {
        template: '<tags></tags>',
        title: 'Tags',
    });
}]);

app.component('tags', {
    templateUrl: tag_list_template_url,
    controller: function($http, $location, HelperService, $scope, $routeParams, $rootScope, $location) {
        $scope.loading = true;
        var self = this;
        self.hasPermission = HelperService.hasPermission;
        $http({
            url: laravel_routes['getTags'],
            method: 'GET',
        }).then(function(response) {
            self.tags = response.data.tags;
            $rootScope.loading = false;
        });
        $rootScope.loading = false;
    }
});