<script>
  document.addEventListener('DOMContentLoaded', function () {
    if (navigator.geolocation) {
      navigator.geolocation.getCurrentPosition(function (position) {
        const latField = document.getElementById('doctor-latitude');
        const lonField = document.getElementById('doctor-longitude');
        if (latField && lonField) {
          latField.value = position.coords.latitude;
          lonField.value = position.coords.longitude;
        }
      });
    }
  });
</script>
