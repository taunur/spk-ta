<?php

namespace App\Http\Controllers\Admin;

use App\Exports\StudentsExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StudentRequest;
use App\Http\Requests\Admin\StudentUpdateRequest;
use App\Imports\StudentsImport;
use App\Models\Kelas;
use App\Models\Student;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    // pagination
    protected $limit = 10;
    protected $fields = array('students.*');
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // mengurutkan
        $students = Student::orderby('kelas_id')
            ->orderby('name');


        if (request('search')) {
            $students->join('kelas', 'kelas.id', '=', 'students.kelas_id')
                ->where('students.name', 'LIKE', '%' . request('search') . '%')
                ->orWhere('students.nis', 'LIKE', '%' . request('search') . '%')
                ->orWhere('students.gender', 'LIKE', '%' . request('search') . '%')
                ->orWhere('kelas.kelas_name', 'LIKE', '%' . request('search') . '%')
                ->get();
        }

        // Get value halaman yang dipilih dari dropdown
        $page = $request->query('page', 1);

        // Tetapkan opsi dropdown halaman yang diinginkan
        $perPageOptions = [5, 10, 15, 20, 25];

        // Get value halaman yang dipilih menggunaakan the query parameters
        $perPage = $request->query('perPage', $perPageOptions[1]);

        // Paginasi hasil dengan halaman dan dropdown yang dipilih
        $students = $students->paginate($perPage, $this->fields, 'page', $page);

        return view('pages.admin.student.data', [
            'title'           => 'Data Karyawan',
            'students'        => $students,
            'perPageOptions'  => $perPageOptions,
            'perPage'         => $perPage
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $kelases = Kelas::all();

        return view('pages.admin.student.create', [
            'title'     => 'Tambah Data Karyawan',
            'kelases'   => $kelases,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StudentRequest $request)
    {
        $validatedData = $request->validated();

        // dd($validatedData);
        Student::create($validatedData);

        return redirect('/dashboard/employee')
            ->with('success', 'Karyawan baru telah ditambahkan!');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $student = Student::FindOrFail($id);
        $kelases = Kelas::all();

        return view('pages.admin.student.edit', [
            'title' => "Edit data $student->name",
            'student' => $student,
            'kelases' => $kelases
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(StudentUpdateRequest $request, Student $employee)
    {
        $validatedData = $request->validated();

        Student::where('id', $employee->id)->update($validatedData);

        return redirect('/dashboard/employee')
            ->with('success', 'Karyawan yang dipilih telah diperbarui!');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Student $employee)
    {
        Student::destroy($employee->id);

        return redirect('/dashboard/employee')
            ->with('success', 'Karyawan yang dipilih telah dihapus!');
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xls,xlsx'
        ]);

        $file = $request->file('file')->store('temp');

        try {
            $import = new StudentsImport;
            $import->import($file);
            if ('kelas_name' === null) {
                dd($import->errors());
            } else {
                return redirect('/dashboard/employee')->with('success', 'Berkas Karyawan Berhasil Diimpor!');
            }
            dd($import);
        } catch (\Exception $e) {
            return back()->withError($e->getMessage())->withInput();
        }
    }

    public function export()
    {
        return Excel::download(new StudentsExport(), 'Data Siswa.xlsx');
    }
}
