<div>
    <div class="uk-panel-box uk-panel-card">
        <div class="uk-panel-box-header uk-flex uk-flex-middle">
            <strong class="uk-panel-box-header-title uk-flex-item-1">
                Mobile User Statistics
            </strong>
        </div>
        <div>
            <canvas id="myChart" width="200" height="200"></canvas>
        </div>
    </div>
</div>
<script type="text/javascript">
    var ctx = document.getElementById('myChart');
    var myChart = new Chart(ctx, {
        type: 'pie',
        data: {
            labels: [
                'PRO',
                'LITE',
            ],
            datasets: [{
                label: 'User statistics',
                data: [<?php echo $pro_count?>, <?php echo $lite_count?>],
                backgroundColor: [
                    'rgb(255, 99, 132)',
                    'rgb(54, 162, 235)',
                ],
                hoverOffset: 4
            }]
        },
    });
</script>
