<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Semester Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
    <link href="https://cdn.datatables.net/1.13.1/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/table.css') }}">
</head>
<body>
    @include('admin.navadmin')

    <div class="d-flex">
        <div class="main-content flex-grow-1 p-4">
            <div class="main-container">
                <div class="row">
                    <div class="col-10">
                        <h1 class="fw-bold text-warning mb-4">{{ $organization }} ORGANIZATION</h1>
                    </div>
                    <div class="col-2">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <a href="{{ route('admin.paymenthistory') }}" class="btn btn-primary">
                                <i class="bi bi-arrow-left"></i> Back
                            </a>
                        </div>
                    </div>
                </div>
                <div class="line"></div>
                <br>
                <div class="row">
                    <div class="col-10">
                        @if ($semesters->isNotEmpty())
                            <h1 class="fw-bold text-primary mb-4">
                                {{ $currentSemester->semester }} {{ $currentSemester->academic_year }}
                            </h1>
                        @else
                            <h1 class="text-danger">No semester records found for this organization.</h1>
                        @endif
                    </div>
                    <div class="col-2">
                    @if ($currentSemester)
                        <form method="GET" action="{{ route('admin.paymenthistorylist.pdf') }}" id="pdfDownloadForm" class="d-inline">
                            <input type="hidden" name="semester_id" value="{{ $currentSemester->id }}">
                            <input type="hidden" name="filter" value="{{ request('filter') }}">
                            <input type="hidden" name="search" id="searchInput">
                            <input type="hidden" name="order_column" id="orderColumnInput">
                            <input type="hidden" name="order_dir" id="orderDirInput">
                            <button type="submit" class="btn btn-success">
                                Download PDF
                            </button>
                        </form>

                    @endif

                    </div>
                </div>

               
                <br>
            </div>

            @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif
            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <strong>Whoops!</strong> Please fix the following errors:
                    <ul class="mb-0 mt-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @php
                if (!function_exists('ordinal')) {
                    function ordinal($number)
                    {
                        $ends = ['th','st','nd','rd','th','th','th','th','th','th'];
                        if (($number % 100) >= 11 && ($number % 100) <= 13) {
                            return $number . 'th';
                        }
                        return $number . $ends[$number % 10];
                    }
                }
            @endphp

            <div class="d-flex justify-content-between-table align-items-center mb-3 flex-wrap gap-2">
                <!-- Filter Dropdown -->
                <form method="GET" action="{{ route('admin.paymenthistorylist') }}">
                    <!-- Preserve semester_id -->
                    <input type="hidden" name="semester_id" value="{{ request('semester_id') }}">
                    
                    <select name="filter" class="form-select" style="min-width: 200px;" onchange="this.form.submit()">
                        <option value="">Show All</option>
                        @foreach ($groupedSections as $year => $sections)
                            <option value="year_{{ $year }}"
                                {{ request('filter') == 'year_'.$year ? 'selected' : '' }}>
                                {{ ordinal($year) }} Year
                            </option>
                            @foreach ($sections as $sec)
                                <option value="section_{{ $sec }}"
                                    {{ request('filter') == 'section_'.$sec ? 'selected' : '' }}>
                                    {{ ordinal((int) substr($sec, 2, 1)) }} Year - {{ $sec }}
                                </option>
                            @endforeach
                        @endforeach
                    </select>
                </form>

                <!-- DataTable Search bar is automatically included -->
            </div>

            <!-- Students Table -->
            <table id="studentsTable" class="table table-striped table-bordered table-hover align-middle">
                <thead>
                    <tr>
                        <th>Student Id</th>
                        <th>Last Name</th>
                        <th>First Name</th>
                        
                        <th>Section</th>
                        <th>Payment Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($students as $student)
                        <tr>
                            <td>{{ $student->id_number }}</td>
                            <td>{{ $student->last_name }}</td>
                            <td>{{ $student->first_name }}</td>
                           
                            <td>{{ $student->section }}</td>
                           <td>
                                <span class="badge rounded-pill fs-6
                                    {{ $student->pivot->payment_status == 'Paid' ? 'bg-success' : 'bg-danger' }}">
                                    {{ $student->pivot->payment_status }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center">No students found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            
          

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- DataTables Bootstrap 5 JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.1/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.1/js/dataTables.bootstrap5.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const toggleBtn = document.querySelector('.toggle-btn');
            const sidebar = document.querySelector('.sidebar');
            const mainContent = document.querySelector('.main-content');

            // Restore sidebar state from localStorage
            const isSidebarOpen = localStorage.getItem('sidebarOpen') === 'true';
            if (isSidebarOpen) {
                sidebar.classList.add('open');
                if (mainContent) mainContent.classList.add('shifted');
            }

            // Toggle sidebar and update localStorage
            toggleBtn.addEventListener('click', function () {
                sidebar.classList.toggle('open');
                if (mainContent) mainContent.classList.toggle('shifted');

                // Save the state
                localStorage.setItem('sidebarOpen', sidebar.classList.contains('open'));
            });
        });
    </script>
    <script>
       $(document).ready(function () {
    const table = $('#studentsTable').DataTable();

    $('#pdfDownloadForm').on('submit', function () {
        const searchVal = table.search();
        const order = table.order()[0]; // [index, direction]

        $('#searchInput').val(searchVal);
        $('#orderColumnInput').val(order[0]);
        $('#orderDirInput').val(order[1]);
    });
});


    </script>
</body>
</html>
