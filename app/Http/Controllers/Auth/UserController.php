<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserTheme;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;

class UserController extends Controller
{
    protected $imageUniqueName;
    /**
     * Display a listing of the resource.
     *
     * @return Application|Factory|View
     */
    public function index()
    {
        $userType = Auth::user()->user_type;

        $users = User::when($userType == 2, function ($query) {
            return $query->where('user_type', 2)->get();
        })->when($userType == 1, function ($query) {
            return $query->where('user_type', '>=', 1)->get();
        })->when($userType == 0, function ($query) {
            return $query->get();
        });

        return view('auth.index', compact('users'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Application|Factory|View
     */
    public function create()
    {
        $user = new User();
        $themes = UserTheme::all();

        return view('auth.create', compact('user', 'themes'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function store(Request $request): RedirectResponse
    {

        $data = $this->validateRequest();

        if(File::isFile($request->image)) {
            $data['image'] = $this->fileUpload(request());
        }

        $data['password'] = Hash::make($data['password']);
        User::create($data);

        return redirect()->route('users.index')->with('success', 'User created successfully.');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param User $user
     * @return Application|Factory|View
     */
    public function edit(User $user)
    {
        $themes = UserTheme::all();
        return view('auth.edit', compact('user', 'themes'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param User $user
     * @return RedirectResponse
     */
    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $this->validateRequest();

        if(File::isFile($request->image)) {
            $this->deletePreviousImage($user->id);
            $data['image'] = $this->fileUpload(request());
        }

        $data['password'] = Hash::make($data['password']);

        $user->update($data);

        return redirect()->route('users.index')->with('success', 'User updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param User $user
     * @return RedirectResponse
     */
    public function destroy(User $user): RedirectResponse
    {
        $user = User::findOrFail($user->id);
        $user->delete();

        return redirect()->route('users.index')->with('success', 'User deleted successfully.');
    }

    protected function validateRequest()
    {
        return tap(request()->validate([
            'name'              => ['required', 'string', 'max:255'],
            'email'             => ['required', 'string', 'email', 'max:255'],
            'contact_number'    => ['required', 'string', 'max:15'],
            'password'          => ['required', 'string', 'min:8', 'confirmed'],
            'image'             => 'nullable|mimes:jpeg,jpg,png',
            'user_type'         => ['required'],
            'theme_id'          => ['nullable'],
        ]),function () {

            if(request()->isMethod('POST')) {
                request()->validate([
                    'email'     => ['required', 'string', 'email', 'max:255', 'unique:users'],
                ]);
            }

            // Unique value check for update
            if(request()->isMethod('PATCH')) {
                // Finding values
                $user = request('user');

                $existingEmail = User::where('email', $user->email)->firstOrFail();

                // strcmp() is case-sensitive function, this function return 0
                if(strcmp(request()->email, $existingEmail['email'])) {
                    request()->validate([
                        'email'     => ['required', 'string', 'email', 'max:255', 'unique:users'],
                    ]);
                } else {
                    return false;
                }
            }

        });
    }

    //Unlink existing image
    protected function deletePreviousImage($id): bool
    {
        $user = User::where('id', $id)
                    ->where('user_type','!=', 0)
                    ->first();
        if($user) {
            if(file_exists(public_path().'/assets/images/auth/'.$user->image)) {
                unlink(public_path().'/assets/images/auth/'.$user->image);
            }
        }

        return true;
    }

    protected function fileUpload($request): string
    {

        if(File::isFile($request->image)) {
            $image	                = $request->file('image');
            $ext                    = $image->getClientOriginalExtension();
            $this->imageUniqueName  = Str::random(40).'.'.$ext;
            $uploadPath 	        = public_path().'/assets/images/auth/'.$this->imageUniqueName;
            //Uploaded image resize
            Image::make($image)->resize(225,225)->save($uploadPath);
        }

        return $this->imageUniqueName;
    }
}
