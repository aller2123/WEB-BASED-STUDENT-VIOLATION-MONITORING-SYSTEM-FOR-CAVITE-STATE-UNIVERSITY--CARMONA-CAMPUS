<table class="table table-hover table-bordered">
    <thead>
        <tr>
            <th>Full Name</th>
            <th>Year & Section</th>
            <th>Program</th>
            <th>Type of Violation</th>
            <th>Status</th>
            <th>Times of violation</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($archivedViolations as $violation): ?>
            <tr>
                <td><?= htmlspecialchars($violation['full_name']) ?></td>
                <td><?= htmlspecialchars($violation['year_and_section']) ?></td>
                <td><?= htmlspecialchars($violation['program_name']) ?></td>
                <td><?= htmlspecialchars($violation['type_of_violation']) ?></td>
                <td><?= htmlspecialchars($violation['status']) ?></td>
                <td><?= $violation['count'] ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
