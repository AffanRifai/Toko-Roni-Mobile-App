document.addEventListener("DOMContentLoaded", function () {

    const searchInput = document.getElementById("search-input");
    const roleFilter = document.getElementById("role-filter");
    const statusFilter = document.getElementById("status-filter");
    const rows = document.querySelectorAll(".user-row");

    function filterUsers() {
        const searchValue = searchInput.value.toLowerCase();
        const roleValue = roleFilter.value;
        const statusValue = statusFilter.value;

        rows.forEach(row => {
            const name = row.dataset.name;
            const email = row.dataset.email;
            const role = row.dataset.role;
            const status = row.dataset.status;

            const matchSearch =
                name.includes(searchValue) ||
                email.includes(searchValue);

            const matchRole =
                roleValue === "" || role === roleValue;

            const matchStatus =
                statusValue === "" || status === statusValue;

            if (matchSearch && matchRole && matchStatus) {
                row.style.display = "";
            } else {
                row.style.display = "none";
            }
        });
    }

    searchInput.addEventListener("input", filterUsers);
    roleFilter.addEventListener("change", filterUsers);
    statusFilter.addEventListener("change", filterUsers);

});
