// Call the dataTables jQuery plugin
let table;
$(document).ready(function () {
  configuration && (configuration['drawCallback'] = function() {
    $('[data-bs-toggle="tooltip"]', table.table().node()).tooltip();
  });
  table = $('#dataTable').DataTable(configuration || {});
});
