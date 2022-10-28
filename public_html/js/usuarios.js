var app = angular.module('usuarios', []);


app.controller('usuarios', function ($scope, $http) {
    $scope.dato = {};
    $scope.datos = [];
    $scope.createForm = {};
    $scope.editForm = {};
    
    $http({
        url: 'get-usuarios',
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
        },
    }).then(
        function successCallback(response) {
            console.log('index', response);
            $scope.datos = response.data.users;
            // $scope.perfiles = response.data.perfiles;
        },
        function errorCallback(response) {
            console.log(response);
            if (response.status === 422) {
                let mensaje = '';
                for (let i in response.data.errors) {
                    mensaje += response.data.errors[i] + '\n';
                }
                swal('Mensaje del Sistema', mensaje, 'error');
            } else {
                swal(
                    'Mensaje del Sistema',
                    response.data.message,
                    response.data.status
                );
            }
        }
    );

    $scope.create = function () {

        $http({
            url: 'usuarios/create',
            method: 'POST',
            data: {
                formulario_crear: 'usuarios_crear'
            },
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },
        }).then(
            function successCallback(response) {
                console.log(response);
                $scope.formulario_crear = response.data.formulario_crear;
                // $scope.createForm.estatus = true;
                // $scope.createForm.visible = true;
                $('#agregarModal').modal('show');
                // setTimeout(() => {
                //     $('#createForm').trigger('reset');
                // }, 500);
            },
            function errorCallback(response) {
                console.log(response);
                if (response.status === 422) {
                    let mensaje = '';
                    for (let i in response.data.errors) {
                        mensaje += response.data.errors[i] + '\n';
                    }
                    swal('Mensaje del Sistema', mensaje, 'error');
                } else {
                    swal(
                        'Mensaje del Sistema',
                        response.data.message,
                        response.data.status
                    );
                }
            }
        );
    }

    $scope.store = function () {
        console.log('name:', $scope.createForm);
        //return;
        $http({
            url: 'usuarios',
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },
            data: $scope.createForm
        }).then(
            function successCallback(response) {
                console.log(response);
                $scope.datos = [...$scope.datos, response.data.user];
                $scope.createForm = {}; 
                //$('#createForm').trigger('reset');
                $('#agregarModal').modal('hide');
                swal(
                    'Mensaje del Sistema',
                    response.data.message,
                    response.data.status
                );
            },
            function errorCallback(response) {
                console.log(response);
                //$('#agregarModal').modal('hide');
                
                if (response.status === 422) {
                    let mensaje = '';
                    for (let i in response.data.errors) {
                        mensaje += response.data.errors[i] + '\n';
                    }
                    swal('Mensaje del Sistema', mensaje, 'error');
                } else {
                    swal(
                        'Mensaje del Sistema',
                        response.data.message,
                        response.data.status
                    );
                }
                
            }
        );
    }

    $scope.edit = function (usuario) {
        console.log('usuario', usuario);
        if(usuario.name) $scope.editForm['name'] = usuario.name;
        if(usuario.email) $scope.editForm['email'] = usuario.email;
        if(usuario.id) $scope.editForm['id'] = usuario.id;
        // if(usuario.direccion) $scope.editForm['direccion'] = usuario.direccion;
        // if(usuario.edad) $scope.editForm['edad'] = parseInt(usuario.edad);
        // if(usuario.perfil_id) $scope.editForm['perfil_id'] = usuario.perfil_id;
        // $scope.editForm.estatus = (usuario.estatus) ? true:false;
        // $scope.editForm.visible = (usuario.visible) ? true:false;
        
        console.log('EditForm', $scope.editForm);
        $http({
            url: 'usuarios/edit',
            method: 'POST',
            data: {
                formulario_editar: 'usuarios_editar'
            },
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },
        }).then(
            function successCallback(response) {
                console.log(response);
                $scope.formulario_editar = response.data.formulario_editar;
                $('#editarModal').modal('show');
            },
            function errorCallback(response) {
                console.log(response);
                if (response.status === 422) {
                    let mensaje = '';
                    for (let i in response.data.errors) {
                        mensaje += response.data.errors[i] + '\n';
                    }
                    swal('Mensaje del Sistema', mensaje, 'error');
                } else {
                    swal(
                        'Mensaje del Sistema',
                        response.data.message,
                        response.data.status
                    );
                }
            }
        );
    }

    $scope.update = function () {

        $http({
            url: `usuarios`,
            method: 'PUT',
            data: $scope.editForm,
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },
        }).then(
            function successCallback(response) {
                console.log('response: ', response);
                $scope.dato = response.data.user;
                $scope.datos = $scope.datos.map(usuario => (usuario.id == response.data.user.id) ? usuario = response.data.user : usuario);
                $('#editarModal').modal('hide');
                swal(
                    'Mensaje del Sistema',
                    response.data.message,
                    response.data.status
                );
            },
            function errorCallback(response) {
                console.log(response);
                if (response.status === 422) {
                    let mensaje = '';
                    for (let i in response.data.errors) {
                        mensaje += response.data.errors[i] + '\n';
                    }
                    swal('Mensaje del Sistema', mensaje, 'error');
                } else {
                    swal(
                        'Mensaje del Sistema',
                        response.data.message,
                        response.data.status
                    );
                }
            }
        );
    }

    $scope.confirmarEliminar = function (usuario) {
        $scope.dato = usuario;
        $('#nombre-usuario').html(usuario.name);
        $('#eliminarModal').modal('show');
    }

    $scope.delete = function () {
        console.log('usuario: ', $scope.dato);

        $http({
            url: `usuarios/${$scope.dato.id}`,
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },
        }).then(
            function successCallback(response) {
                console.log(response);
                $scope.datos = $scope.datos.filter(usuario => usuario.id !== $scope.dato.id);
                $('#eliminarModal').modal('hide');
                swal(
                    'Mensaje del Sistema',
                    response.data.message,
                    response.data.status
                );
            },
            function errorCallback(response) {
                console.log(response);
                if (response.status === 422) {
                    let mensaje = '';
                    for (let i in response.data.errors) {
                        mensaje += response.data.errors[i] + '\n';
                    }
                    swal('Mensaje del Sistema', mensaje, 'error');
                } else {
                    swal(
                        'Mensaje del Sistema',
                        response.data.message,
                        response.data.status
                    );
                }
            }
        );
        
    }

    $('#editarModal').on('hidden.bs.modal', function () {
        console.log('haz algo');
    });
});

app.filter('activoInactivo', function() {
    return function(input) {
        return input ? 'Activo' : 'Inactivo';
    }
});
app.filter('siNo', function() {
    return function(input) {
        return input ? 'Si' : 'No';
    }
});
