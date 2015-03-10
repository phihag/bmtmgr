document.addEventListener("DOMContentLoaded", function() {
    var request = indexedDB.open("tournaments");
    request.onerror = function(event) {

    };
    request.onsuccess = function(event) {
      db = event.target.result;
    };
});