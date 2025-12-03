<script>
    function openTab(evt, tabName) {
        // Hide all tab contents
        var tabcontent = document.getElementsByClassName("tabcontent");
        for (var i = 0; i < tabcontent.length; i++) {
            tabcontent[i].classList.remove("active");
        }

        // Remove "active" class from all tab buttons
        var tablinks = document.getElementsByClassName("tablinks");
        for (var i = 0; i < tablinks.length; i++) {
            tablinks[i].className = tablinks[i].className.replace(" active", "");
        }

        // Show the selected tab and mark button as active
        document.getElementById(tabName).classList.add("active");
        evt.currentTarget.className += " active";
    }
</script>