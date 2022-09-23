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
    if (document.getElementById('motdepasse')) {

        const password = document.getElementById('motdepasse');
        const eye = document.getElementById('viewpassword');
        const eyepassword = document.getElementById('eyepassword');
        eye.addEventListener("click", function () {
            if (password.attr("type") === "text") {
                password.attr('type', 'password');
                eyepassword.addClass("fa-eye-slash");
                eyepassword.removeClass("fa-eye");
            } else if (password.attr("type") === "password") {
                password.attr('type', 'text');
                eyepassword.removeClass("fa-eye-slash");
                eyepassword.addClass("fa-eye");
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
            }, 10000);

        });
    });


    function serverJson(id) {
      //  const progressbar = document.getElementById('progressbar' + id);
        let url = `/admin/ssh/sshjson/${id}`;
        fetch(url).then(response => {
            if (response.redirected)
                window.location.href = response.url;
            return  response.json()}).then(data => {
            // console.log(data)
            // console.log(data.cpu)



            // ram+=obj.ram;
            // ramuser+=obj.ramuse;
            // ramfree+=obj.ramfree;
            // ramuser+=obj.ramuse;
            document.getElementById(id).innerHTML = `${data.ramfree}/${data.ram} ${data.ramuse} Libre`;
            let cpuusage = parseFloat(data.cpuusage.replace(',','.'));
            document.getElementById('uptime' + id).innerHTML = `${(parseFloat(data.cpuusage)>100?'100':data.cpuusage)} %`;
            document.getElementById('progressbar' + id).innerHTML =`${(parseFloat(data.cpuusage)>100?'100':data.cpuusage)} %`;
            document.getElementById('progressbar' + id).style =`width: ${parseFloat(data.cpuusage)}%`;
            document.getElementById('progressbar' + id).ariaValueNow = parseFloat(data.cpuusage);
            console.log(cpuusage);
            if(parseFloat(data.cpuusage.replace(',','.')) >= 80)
            {
                document.getElementById('progressbar' + id).classList.remove('bg-success')
                document.getElementById('progressbar' + id).classList.add('bg-danger')
            }else if(parseFloat(data.cpuusage.replace(',','.')) >= 60)
            {
                document.getElementById('progressbar' + id).classList.remove('bg-success')
                document.getElementById('progressbar' + id).classList.remove('bg-danger')
                document.getElementById('progressbar' + id).classList.add('bg-warning')
            }else {
                document.getElementById('progressbar' + id).classList.remove('bg-warning')
                document.getElementById('progressbar' + id).classList.remove('bg-danger')
                document.getElementById('progressbar' + id).classList.add('bg-success')
            }


            document.getElementById('disk' + id).innerHTML = `${data.diskfree}/${data.disk} ${data.diskuse} utilisé`;


        });
    }

}

if (document.getElementById('gauge')) {
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
        }, 20000);


    });
}


function serverJsonGauge(id) {
    let url = `/admin/ssh/sshjson/${id}`;
    fetch(url).then(response => {
        if (response.redirected)
            window.location.href = response.url;
        return  response.json()}).then(data => {

        // console.log(data)
        let ramfree = data.ramfree.replace(",", ".");
        console.log(parseFloat(ramfree))
        let ctx = document.getElementById("gauge");
        let myChart = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: ['RAM utilisé', 'RAM LIBRE'],
                datasets: [{
                    data: [data.ramfree.replace(",", "."), data.ramuse.replace(",", ".")],
                    backgroundColor: [
                        'rgba(255,0,0,0.7)',
                        'rgba(41,224,20,0.7)'
                    ],
                    borderColor: [
                        'rgba(255,0,0,0.7)',
                        'rgba(41,224,20,0.7)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                //cutoutPercentage: 40,
                responsive: true,
                tooltips: {
                    callbacks: {
                        label: (tooltipItem, chart) => {
                            const realValue = chart.datasets[tooltipItem.datasetIndex].data[tooltipItem.index]
                            const customValue = realValue + ' Giga';
                            const label = chart.labels[tooltipItem.index] + ':';
                            return label + customValue;
                        }
                    }
                }
            }
        });

        let ctx2 = document.getElementById("disk");
        let myChart2 = new Chart(ctx2, {
            type: 'pie',
            data: {
                labels: ['Disk utilisé', 'Disk LIBRE'],
                datasets: [{
                    data: [data.diskuse.replace(",", "."), data.diskfree.replace(",", ".")],
                    backgroundColor: [
                        'rgba(255,0,0,0.7)',
                        'rgba(41,224,20,0.7)'
                    ],
                    borderColor: [
                        'rgba(255,0,0,0.7)',
                        'rgba(41,224,20,0.7)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                //cutoutPercentage: 40,
                responsive: true,
                tooltips: {
                    callbacks: {
                        label: (tooltipItem, chart) => {
                            const realValue = chart.datasets[tooltipItem.datasetIndex].data[tooltipItem.index]
                            const customValue = realValue + ' Giga';
                            const label = chart.labels[tooltipItem.index] + ':';
                            return label + customValue;
                        }
                    }
                }
            }
        });
        console.log(data.cpuusage)
        let cpuuse = data.cpuusage.replace(",", ".");
        Chart.pluginService.register({
            beforeDraw: function (chart) {
                if (chart.config.options.elements.center) {
                    //Get ctx from string
                    let ctx = chart.chart.ctx;

                    //Get options from the center object in options
                    let centerConfig = chart.config.options.elements.center;
                    let fontStyle = centerConfig.fontStyle || 'Arial';
                    let txt = centerConfig.text;
                    let color = centerConfig.color || '#000';
                    let sidePadding = centerConfig.sidePadding || 20;
                    let sidePaddingCalculated = (sidePadding / 100) * (chart.innerRadius * 2)
                    //Start with a base font of 30px
                    ctx.font = "30px " + fontStyle;

                    //Get the width of the string and also the width of the element minus 10 to give it 5px side padding
                    let stringWidth = ctx.measureText(txt).width;
                    let elementWidth = (chart.innerRadius * 2) - sidePaddingCalculated;

                    // Find out how much the font can grow in width.
                    let widthRatio = elementWidth / stringWidth;
                    let newFontSize = Math.floor(30 * widthRatio);
                    let elementHeight = (chart.innerRadius * 2);

                    // Pick a new font size so it will not be larger than the height of label.
                    let fontSizeToUse = Math.min(newFontSize, elementHeight);

                    //Set font settings to draw it correctly.
                    ctx.textAlign = 'center';
                    ctx.textBaseline = 'middle';
                    let centerX = ((chart.chartArea.left + chart.chartArea.right) / 2);
                    let centerY = ((chart.chartArea.top + chart.chartArea.bottom) / 2);
                    ctx.font = 40 + "px " + fontStyle;
                    ctx.fillStyle = color;

                    //Draw text in center
                    ctx.fillText(txt, centerX, centerY);
                }
            }
        });


        let config = {
            type: 'doughnut',
            data: {
                labels: [
                    "Charge",
                    "Libre",
                ],
                datasets: [{
                    data: [(parseFloat(cpuuse)>100?'100':cpuuse), (100 - (parseFloat(cpuuse)>100?'100':cpuuse))],
                    backgroundColor: [
                        'rgba(255,0,0,0.7)',
                        'rgba(41,224,20,0.7)'
                    ],
                    hoverBackgroundColor: [
                        'rgba(255,0,0,0.7)',
                        'rgba(41,224,20,0.7)'
                    ]
                }]
            },
            options: {
                responsive: true,
                elements: {
                    center: {
                        text: (parseFloat(cpuuse)>100?'100':cpuuse)  + '%',
                        color: '#FF6384', // Default is #000000
                        fontStyle: 'Arial', // Default is Arial
                        sidePadding: 20 // Defualt is 20 (as a percentage)
                    }
                },
                tooltips: {
                    callbacks: {
                        label: (tooltipItem, chart) => {
                            const realValue = chart.datasets[tooltipItem.datasetIndex].data[tooltipItem.index]
                            const customValue = realValue.toFixed(2) + ' %';
                            const label = chart.labels[tooltipItem.index] + ':';
                            return label + customValue;
                        }
                    }
                }
            }
        };


        let ctx4 = document.getElementById("cpu").getContext("2d");
        let myChart4 = new Chart(ctx4, config);


        // myChart.update()


    });


}

if (document.getElementById('etat')) {
    ping();
    setInterval(() => {
        ping();
    }, 20000);
}

function ping() {
    const id = document.getElementById('server').value;
    const etat = document.getElementById('etat');
    const start = document.getElementById('start');
    const stop = document.getElementById('stop');
    let url = `/admin/server/ping/${id}`;
    fetch(url).then(response => response.json().then(data => {
        console.log(data);
        if(data.ping) {

            etat.textContent = data.ping;
        }else{
            etat.textContent = `Hors ligne`;
        }
        if(data.ping==="Hors ligne")
        {
            start.style.display =``;
            stop.style.display =`none`;
        }else{
            start.style.display =`none`;
            stop.style.display =``;
        }

    }));
}

if (document.getElementById('ippower')) {
    ippower();
    setInterval(() => {
        ippower();
    }, 30000);
}

function ippower() {
    const id = document.getElementById('server').value;
    const etat = document.getElementById('ippower');
    const stop = document.getElementById('stop');
    const start = document.getElementById('start');
    const restart = document.getElementById('restart');
    let url = `/admin/server/pingippower/${id}`;
    fetch(url).then(response => response.json().then(data => {
        console.log(data);

        if(data.ippower) {
            etat.textContent = data.ippower;


        }else{
            etat.textContent = `Inactif`;
        }

        if(data.ippower==="Inactif")
        {
            stop.style.display =`none`;
            restart.style.display =`none`;
            start.style.display =``;
        }

        if(data.ippower==="Actif")
        {
            stop.style.display =``;
            restart.style.display =``;
            start.style.display =`none`;
        }

    }));
}

if (document.getElementById('restart')) {
    const restart = document.getElementById('restart');
    const ippower = document.getElementById('ippower');
    const etat = document.getElementById('etat');
    const id = document.getElementById('server').value;
    const etatelec = document.getElementById('etatelec');


    restart.addEventListener("click", () => {
        etat.innerHTML = `Hors ligne`;
        ippower.innerHTML = `Inactif`;
        let url = `/admin/server/restartippower/${id}`;

        etatelec.innerHTML = "Redémarrage en cours";

        fetch(url).then(response => response.json().then(data => {

            if(data.ippower==="")
            {

            }
            if (data.ippower === "Inactif") {
                setTimeout(function () {
                    etatelec.innerHTML  = "Redémarrage terminé";
                }, 120000);
                etatelec.innerHTML

                // On l'efface 8 secondes plus tard
                setTimeout(function () {
                    etatelec.innerHTML = "";
                }, 160000);
            }
        }));
    })
}

if (document.getElementById('start')) {
    const start = document.getElementById('start');
    const ippower = document.getElementById('ippower');
    const etat = document.getElementById('etat');
    const id = document.getElementById('server').value;
    const etatelec = document.getElementById('etatelec');


    start.addEventListener("click", () => {
        let url = `/admin/server/startippower/${id}`;

        etatelec.innerHTML = "Démarrage en cours";

        fetch(url).then(response => response.json().then(data => {

            if(data.ippower==="Actif")
            {
                ippower.innerHTML = `Actif`;
                start.style.display =``;
                    etatelec.innerHTML  = "Démarrage terminé";
                setTimeout(function () {
                    etatelec.innerHTML = "";
                }, 10000);

            }

        }));
    })
}

if (document.getElementById('stop')) {
    const stop = document.getElementById('stop');
    const start = document.getElementById('start');
    const restart = document.getElementById('restart');
    const ippower = document.getElementById('ippower');
    const etat = document.getElementById('etat');
    const id = document.getElementById('server').value;
    const etatelec = document.getElementById('etatelec');


    stop.addEventListener("click", () => {
        let url = `/admin/server/stopippower/${id}`;
        etat.innerHTML = `Hors ligne`;
        etatelec.innerHTML = "Arrêt en cours";
        stop.style.display =`none`;
        restart.style.display =`none`;
        start.style.display =``;
        fetch(url).then(response => response.json().then(data => {

            if(data.ippower==="Inactif")
            {
                ippower.innerHTML = `Inactif`;

                    etatelec.innerHTML  = "Arrêt terminé";
                setTimeout(function () {
                    etatelec.innerHTML = "";
                }, 10000);

            }

        }));
    })
}
