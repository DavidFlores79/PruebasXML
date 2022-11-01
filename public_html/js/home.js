var app = angular.module("home", ["angularUtils.directives.dirPagination"]);

app.controller("home", function ($scope, $http) {
  $scope.currentPage = 1;
  $scope.pageSize = 10;
  $scope.readXml = '';

  $scope.cargarXML = function (event) {
    event.preventDefault();

    var selectedFile = document.getElementById("xml").files;
    var base64String = "";
    if (selectedFile.length > 0) {
      var fileToLoad = selectedFile[0];
      var fileReader = new FileReader();
      var base64;

      fileReader.onload = function (fileLoadedEvent) {
        base64 = fileLoadedEvent.target.result;
        base64String = base64.replace('data:', '').replace(/^.+,/, '').replace('77u/', '');

        let data = {
          "proveedor":"10802",
          "sociedad":"1011",
          "documento":"0123456789",
          "importe_documento": 1150.56,
          "referencia": "1234567890",
          "rfc":"YAK800303JA7",
          "tipo_xml": "I",
          "ejercicio": 2022,
          "xml": base64String,
          "nombre_xml": selectedFile[0].name
        }
        $scope.enviarXML(data)
      };
      fileReader.readAsDataURL(fileToLoad);
    }
  }

  $scope.enviarXML = function (data) {

    $http({
      url: 'cargaxml',
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        Accept: 'application/json',
      },
      data: data,
    }).then(
      function successCallback(response) {
        console.log('response: ', response);                              
        swal(
          'Mensaje del Sistema',
          response.data.message,
          response.data.status
        );
        $('#xml').val('').next('.custom-file-label').html('Escoger Archivo...');
      },
      function errorCallback(response) {
        console.log('error: ', response);
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

  $scope.cargarZIP = function (event) {
    event.preventDefault();

    var selectedFile = document.getElementById("zip").files;
    var base64String = "";
    if (selectedFile.length > 0) {
      var fileToLoad = selectedFile[0];
      var fileReader = new FileReader();
      var base64;

      fileReader.onload = function (fileLoadedEvent) {
        base64 = fileLoadedEvent.target.result;
        base64String = base64.replace('data:', '').replace(/^.+,/, '').replace('77u/', '');

        let data = {
          "proveedor":"10802",
          "sociedad":"1011",
          "zip": base64String,
          "nombre_zip": selectedFile[0].name
        }
        $scope.enviarZIP(data);
        //console.log('data', data);
      };
      fileReader.readAsDataURL(fileToLoad);
    }
  }

  $scope.enviarZIP = function (data) {
    
    $http({
      url: 'cargazip',
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        Accept: 'application/json',
      },
      data : data,
    }).then(
      function successCallback(response) {
        console.log(response);
        $('#zip').val('').next('.custom-file-label').html('Escoger Archivo...');
      },
      function errorCallback(response) {
        console.log('error zip: ', response);
        if (response.status === 422) {
          let mensaje = '';
          for (let i in response.data.errors) {
            mensaje += response.data.errors[i] + '\n';
          }
          swal('Mensaje del Sistema', mensaje, 'error');
        } else if (response.status === 413) {
          swal('Mensaje del Sistema', 'Archivo demasiado grande.', 'warning');
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


});

app.filter("activoInactivo", function () {
  return function (input) {
    return input ? "Activo" : "Inactivo";
  };
});
app.filter("siNo", function () {
  return function (input) {
    return input ? "Si" : "No";
  };
});
app.filter("temperatura", function () {
  return function (value) {
    return value + " °C";
  };
});
app.filter("humedad", function () {
  return function (value) {
    return value + " %";
  };
});
