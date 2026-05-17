document.getElementById("createGoalForm")
.addEventListener("submit", function(e) {

    e.preventDefault();

    const formData = new FormData(this);

    fetch("create_goal.php", {
        method: "POST",
        body: formData
    })
    .then(response => response.json())
    .then(data => {

        document.getElementById("createGoalMessage").innerText =
            data.message;

        if(data.success) {
            document.getElementById("createGoalForm").reset();
        }
    })
    .catch(error => console.log(error));
});


document.getElementById("updateGoalForm")
.addEventListener("submit", function(e) {

    e.preventDefault();

    const formData = new FormData(this);

    fetch("update_goal.php", {
        method: "POST",
        body: formData
    })
    .then(response => response.json())
    .then(data => {

        document.getElementById("updateGoalMessage").innerText =
            data.message;
    })
    .catch(error => console.log(error));
});