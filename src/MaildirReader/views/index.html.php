<?php include 'views/header.html'; ?>
    <h1>Inbox</h1>


    <div class="row">
        <div class="col-md-9">
            <ul>
                <?php
                foreach ($allMails as $mail) { ?>
                    <li class="mb-2"><a
                                href="view?id=<?php echo $mail['id']; ?>"><?php echo $mail['subject']; ?></a><br/>
                        <i><?php echo $mail['from']; ?></i> <small>(<?php echo $mail['date']; ?>)</small></li>
                <?php } ?>
            </ul>
        </div>
        <div class="col-md-3">
            Calendar
            <?php
            $firstDayOfMonth = date('Y-m-01', strtotime($allMailDates[0]));
            $lastDayOfMonth = date('Y-m-t', strtotime($allMailDates[count($allMailDates) - 1]));
            $start = new DateTime($firstDayOfMonth);
            $end = new DateTime($lastDayOfMonth);
            $interval = new DateInterval('P1M'); // 1 month interval
            $period = new DatePeriod($start, $interval, $end);

            // Add the end date if not included
            $end->modify('+1 month'); // To include the end month
            $period = new DatePeriod($start, $interval, $end);
            ?>

            <div class="calendar-container">
                <?php foreach ($period as $date):
                    $firstDay = $date->format('Y-m-01');
                    $lastDay = $date->format('Y-m-t');
                    $monthName = $date->format('F Y');
                    $firstDayOfWeek = date('N', strtotime($firstDay)); // 1 (Monday) to 7 (Sunday)
                    $daysInMonth = $date->format('t');
                    ?>
                    <div class="month-container">
                        <h3><?= $monthName ?></h3>
                        <table class="calendar-table">
                            <thead>
                            <tr>
                                <th>Lun</th>
                                <th>Mar</th>
                                <th>Mie</th>
                                <th>Joi</th>
                                <th>Vin</th>
                                <th>Sâm</th>
                                <th>Dum</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                <?php
                                // Add empty cells for days before the first day of the month
                                for ($i = 1; $i < $firstDayOfWeek; $i++) {
                                    echo '<td></td>';
                                }

                                // Add the days of the month
                                $currentDay = 1;
                                while ($currentDay <= $daysInMonth) {
                                    // Start a new row at the beginning of each week
                                    if (($currentDay + $firstDayOfWeek - 2) % 7 == 0 && $currentDay != 1) {
                                        echo '
                    </tr>
                    <tr>';
                                    }

                                    $currentDate = $date->format('Y-m-').str_pad($currentDay, 2, '0', STR_PAD_LEFT);
                                    $hasMails = in_array($currentDate, $allMailDates) ? 'has-mail' : '';
                                    if (in_array($currentDate, $allMailDates)) {
                                        $dayHTML = '<a href="date?date=' . $currentDate . '">' . $currentDay . '</a>';
                                    }else {
                                        $dayHTML = $currentDay;
                                    }

                                    echo '
                        <td class="'.$hasMails.'">'.$dayHTML.'</td>
                        ';
                                    $currentDay++;
                                }

                                // Fill the remaining cells in the last row
                                $remainingCells = 7 - (($daysInMonth + $firstDayOfWeek - 1) % 7);
                                if ($remainingCells < 7) {
                                    for ($i = 0; $i < $remainingCells; $i++) {
                                        echo '
                        <td></td>
                        ';
                                    }
                                }
                                ?>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                <?php endforeach; ?>
            </div>

        </div>
    </div>

    <nav>
        <ul class="pagination">
            <li class="page-item">
                <?php if ($pagination['page'] > 1) { ?>
                    <a href="index?page=<?php echo $pagination['page'] - 1; ?>">Previous</a>
                <?php } else {
                    echo 'Previous';
                } ?></li>
            <li class="page-item">
                <?php if ($pagination['page'] < $pagination['total']) { ?>
                    <a href="index?page=<?php echo $pagination['page'] + 1; ?>">Next</a>
                <?php } else {
                    echo 'Next';
                } ?></li>
        </ul>
    </nav>

    <style>
        .calendar-container {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }

        .month-container {
            border: 1px solid #ddd;
            padding: 10px;
            border-radius: 5px;
            background: white;
        }

        .calendar-table {
            border-collapse: collapse;
            width: 100%;
        }

        .calendar-table th,
        .calendar-table td {
            border: 1px solid #eee;
            padding: 5px;
            text-align: center;
        }

        .calendar-table th {
            background-color: #f5f5f5;
            font-weight: bold;
        }

        .calendar-table .has-mail {
            background-color: #e3f2fd;
            font-weight: bold;
        }

        .calendar-table .has-mail:hover {
            background-color: #bbdefb;
            cursor: pointer;
        }
    </style>
<?php include 'views/footer.html'; ?>