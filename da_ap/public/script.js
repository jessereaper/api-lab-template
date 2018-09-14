$(document).ready(function() {
  $("#championsForm").submit(function(event) {
    var form = $(this);
    event.preventDefault();
    $.ajax({
      type: "POST",
      url: "http://localhost:8081/api/champions",
      data: form.serialize(), // serializes the form's elements.
      success: function(data) {
        window.location.replace("http://localhost:8081/da_ap/champions");
      }
    });
  });
  $("#championsEditForm").submit(function(event) {
    var form = $(this);
    event.preventDefault();
    $.ajax({
      type: "POST",
      url: "http://localhost:8081/api/champions/" + id,
      data: form.serialize(), // serializes the form's elements.
      success: function(data) {
        window.location.replace("http://localhost:8081/da_ap/champions");
      }
    });
  });
  $(".deletebtn").click(function() {
    if (window.confirm("Do you really want to delete this?")) {
      var id = $(this).attr("data-id");
      $.ajax({
        type: "DELETE",
        url: "http://localhost:8081/api/champions/" + id,
        contentType: "application/json",
        success: function() {
          alert("deleted successful?."),
            location.reload();
        },
      });
    };
  });
  // $( ".deletebtn" ).click(function() {
  // alert( "TODO: build delete handler with confirmation dialog See here for confirmation details:  https://developer.mozilla.org/en-US/docs/Web/API/Window/confirm" );
  // this is for delete button
  // alert($(this).attr("data-id"));
});
