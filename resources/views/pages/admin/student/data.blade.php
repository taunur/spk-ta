@extends('layouts.admin')

@section('content')
{{-- {{ print_r($students) }} --}}
<main>
    <div class="container-fluid px-4">
        <div class="row align-items-center">
            <div class="col-sm-6 col-md-8">
                <h1 class="mt-4">{{ $title }}</h1>
                <ol class="breadcrumb mb-4">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard.index') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">{{ $title }}</li>
                    <li class="breadcrumb-item"><a href="{{ route('team.index') }}">Data Team</a></li>
                </ol>
            </div>
        </div>

        {{-- datatable --}}
        <div class="card mb-4">
            <div class="card-body table-responsive">
                <div class="d-sm-flex align-items-center justify-content-between">
                    <a href="{{ route('employee.create') }}" type="button" class="btn btn-primary mb-3"><i
                            class="fas fa-plus me-1"></i>Karyawan
                    </a>

                    <form action="{{ route('students.import') }}" method="POST" enctype="multipart/form-data"
                        class="ms-auto">
                        @csrf
                        <div class="input-group mb-3">
                            <input type="file" name="file" class="form-control" required>
                            <button type="submit" class="btn btn-success">
                                <i class="fa-solid fa-upload"></i>
                                Karyawan
                            </button>
                            <a class="btn btn-warning" href="{{ route('students.export') }}">
                                <i class="fa-solid fa-download"></i>
                                Karyawan
                            </a>
                        </div>
                    </form>
                </div>

                {{-- validation error file required --}}
                @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                @endif

                {{-- file request --}}
                @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    @foreach ($errors->all() as $error)
                    {{ $error }}
                    @endforeach
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                @endif

                <div class="d-sm-flex align-items-center justify-content-between">
                    <div class="d-sm-flex align-items-center mb-3">
                        <select class="form-select me-3" id="perPage" name="perPage" onchange="submitForm()">
                            @foreach ($perPageOptions as $option)
                            <option value="{{ $option }}" {{ $option==$perPage ? 'selected' : '' }}>
                                {{ $option }}
                            </option>
                            @endforeach
                        </select>
                        <label class="form-label col-lg-7 col-sm-7 col-md-7" for="perPage">entries per page</label>
                    </div>

                    <form action="{{ route('employee.index') }}" method="GET" class="ms-auto float-end">
                        <div class="input-group mb-3">
                            <input type="text" name="search" id="myInput" class="form-control" placeholder="Search..."
                                value="{{ request('search') }}">
                            <button class="btn btn-primary" type="submit">Search</button>
                        </div>
                    </form>
                </div>

                <table class="table table-bordered">
                    <thead class="table-primary">
                        <tr>
                            <th>No</th>
                            <th>NPK</th>
                            <th>Nama Karyawan</th>
                            <th>Jenis Kelamin</th>
                            <th>Team</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if ($students->count())
                        @foreach ($students as $student)
                        <tr>
                            <td scope="row">
                                {{ ($students->currentpage() - 1) * $students->perpage() + $loop->index + 1 }}
                            </td>
                            <td>{{ $student->nis }}</td>
                            <td>{{ Str::ucfirst(Str::upper($student->name)) }}</td>
                            <td>{{ $student->gender }}</td>
                            <td>{{ $student->kelas->kelas_name ?? 'Tidak Punya Kelas' }}</td>
                            <td>
                                <a href="{{ route('employee.edit', $student->id) }}" class="badge bg-warning"><i
                                        class="fa-solid fa-pen-to-square"></i></a>
                                <form action="{{ route('employee.destroy', $student->id) }}" method="POST"
                                    class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button class="badge bg-danger border-0 btnDelete" data-object="siswa"><i
                                            class="fa-solid fa-trash-can"></i></button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                        @else
                        <tr>
                            <td colspan="6" class="text-danger text-center p-4">
                                <h4>Belum ada data Karyawan</h4>
                            </td>
                        </tr>
                        @endif
                    </tbody>
                </table>
                {{ $students->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
</main>

<script>
    function submitForm() {
            var perPageSelect = document.getElementById('perPage');
            var perPageValue = perPageSelect.value;

            var currentPage = {{ $students->currentPage() }};
            var url = new URL(window.location.href);
            var params = new URLSearchParams(url.search);

            params.set('perPage', perPageValue);

            if (!params.has('page')) {
                params.set('page', currentPage);
            }

            url.search = params.toString();
            window.location.href = url.toString();
        }
</script>
@endsection
