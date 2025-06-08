// Call the dataTables jQuery plugin
let table;
$(document).ready(function() {
  table = $('#dataTable').DataTable(configuration || {});
});
