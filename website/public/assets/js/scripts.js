/*!
    * Start Bootstrap - SB Admin v7.0.5 (https://startbootstrap.com/template/sb-admin)
    * Copyright 2013-2022 Start Bootstrap
    * Licensed under MIT (https://github.com/StartBootstrap/startbootstrap-sb-admin/blob/master/LICENSE)
    */
//
// Scripts
// 

window.addEventListener('DOMContentLoaded', event => {

    // Toggle the side navigation
    const sidebarToggle = document.body.querySelector('#sidebarToggle');
    if (sidebarToggle) {
        // Uncomment Below to persist sidebar toggle between refreshes
        // if (localStorage.getItem('sb|sidebar-toggle') === 'true') {
        //     document.body.classList.toggle('sb-sidenav-toggled');
        // }
        sidebarToggle.addEventListener('click', event => {
            event.preventDefault();
            document.body.classList.toggle('sb-sidenav-toggled');
            localStorage.setItem('sb|sidebar-toggle', document.body.classList.contains('sb-sidenav-toggled'));
        });
    }
    // $("#viewpassword").click(function (event) {
    //     if ($('#motdepasse').attr("type") == "text") {
    //         $('#motdepasse').attr('type', 'password');
    //         $('#eyepassword').addClass("fa-eye-slash");
    //         $('#eyepassword').removeClass("fa-eye");
    //     } else if ($('#motdepasse').attr("type") == "password") {
    //         $('#motdepasse').attr('type', 'text');
    //         $('#eyepassword').removeClass("fa-eye-slash");
    //         $('#eyepassword').addClass("fa-eye");
    //     }
    // });

    if (document.getElementById('password')) {

        const password = document.getElementById('password');
        password.addEventListener("click", function () {
            if (password.attr("type") === "text") {
                password.attr('type', 'password');
                password.addClass("fa-eye-slash");
                password.removeClass("fa-eye");
            } else if (password.attr("type") === "password") {
                password.attr('type', 'text');
                password.removeClass("fa-eye-slash");
                password.addClass("fa-eye");
            }
        });
    }
});
if (document.getElementsByClassName('ram')) {
    window.addEventListener('load', (event) => {
        // let ram = document.getElementsByClassName('ram')
        // for(let i=0; i<ram.length;i++)
        // {
        //     console.log(ram[i].childNodes.getAttribute("id"));
        // }
        document.querySelectorAll(".ram").forEach((item) => {

            console.log(item.firstChild.id);
            serverJson(item.firstChild.id)

            setInterval(() => {
                serverJson(item.firstChild.id);
            }, 20000);

        });
    });


    function serverJson(id) {
        let url = `/admin/ssh/sshjson/${id}`;
        fetch(url).then(response => response.json().then(data => {
            // console.log(data)
            // console.log(data.cpu)


            // ram+=obj.ram;
            // ramuser+=obj.ramuse;
            // ramfree+=obj.ramfree;
            // ramuser+=obj.ramuse;
            document.getElementById(id).innerHTML = `${data.ramfree}/${data.ram} ${data.ramuse} Libre`;

            document.getElementById('uptime' + id).innerHTML = `${data.cpu} thread`;
            document.getElementById('disk' + id).innerHTML = `${data.diskfree}/${data.disk} ${data.diskuse} utilisé`;


        }));
    }

}

if(document.getElementById('gauge'))
{
    console.log(document.querySelector('.gauge').id)
    window.addEventListener('load', (event) => {
        // let ram = document.getElementsByClassName('ram')
        // for(let i=0; i<ram.length;i++)
        // {
        //     console.log(ram[i].childNodes.getAttribute("id"));
        // }

            serverJsonGauge(document.querySelector('.gauge').id)

            setInterval(() => {
                serverJsonGauge(document.querySelector('.gauge').id);
            }, 15000);


    });
}


function serverJsonGauge(id) {
    let url = `/admin/ssh/sshjson/${id}`;
    fetch(url).then(response => response.json().then(data => {
        // console.log(data)
        // console.log(data.cpu)
        let ctx = document.getElementById("gauge");
        let myChart = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: ['RAM utilisé', 'RAM LIBRE'],
                datasets: [{
                    data: [parseFloat(data.ramfree), parseFloat(data.ramuse)],
                    backgroundColor: [
                        'rgb(255,0,0)',
                        'rgba(41,224,20,0.88)'
                    ],
                    borderColor: [
                        'rgb(255,0,0)',
                        'rgba(41,224,20,0.88)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                //cutoutPercentage: 40,
                responsive: false,

            }
        });

        let ctx2 = document.getElementById("disk");
        let myChart2 = new Chart(ctx2, {
            type: 'pie',
            data: {
                labels: ['Disk utilisé', 'Disk LIBRE'],
                datasets: [{
                    data: [parseFloat(data.diskuse), parseFloat(data.diskfree)],
                    backgroundColor: [
                        'rgb(255,0,0)',
                        'rgba(41,224,20,0.88)'
                    ],
                    borderColor: [
                        'rgb(255,0,0)',
                        'rgba(41,224,20,0.88)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                //cutoutPercentage: 40,
                responsive: false,

            }
        });


       // myChart.update()


    }));
}