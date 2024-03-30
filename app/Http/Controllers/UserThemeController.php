<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserTheme;
use App\Http\Requests\StoreUserThemeRequest;
use App\Http\Requests\UpdateUserThemeRequest;
use App\Traits\MiddlewareTrait;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class UserThemeController extends Controller
{
    use MiddlewareTrait;

    /**
     * Display a listing of the resource.
     *
     * @return Application|Factory|View
     */
    public function index()
    {
        $themes = UserTheme::all();
        return view('auth.theme.index', compact('themes'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Application|Factory|View
     */
    public function create()
    {

        $theme = new UserTheme();
        $users = User::all();

        return view('auth.theme.create', compact('theme', 'users'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreUserThemeRequest $request
     * @return RedirectResponse
     */
    public function store(StoreUserThemeRequest $request): RedirectResponse
    {

        $data = $this->validateRequest();
        UserTheme::create($data);

        return redirect()->route('themes.index')->with('success', 'User theme created successfully.');
    }

    /**
     * Display the specified resource.
     *
     * @param UserTheme $themes
     * @return Response
     */
    public function show(UserTheme $themes)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param UserTheme $theme
     * @return Application|Factory|View
     */
    public function edit(UserTheme $theme)
    {
        $users = User::all();
        return view('auth.theme.edit', compact('theme', 'users'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateUserThemeRequest $request
     * @param UserTheme $theme
     * @return RedirectResponse
     */
    public function update(UpdateUserThemeRequest $request, UserTheme $theme): RedirectResponse
    {
        /*
            // Validate the request data
            $data = $this->validateRequest();

            // Retrieve the theme to update by its ID
            $themeToUpdate = UserTheme::findOrFail($theme->id);

            // Get the new user ID from the request
            $newUserId = $request->user_id;

            // If the new user ID is already set in other records, set it to null
            UserTheme::where('user_id', $newUserId)->where('id', '!=', $themeToUpdate->id)->update(['user_id' => null]);

            // Update the theme to update with the new user ID
            $themeToUpdate->update($data);
         */

        $data = $this->validateRequest();
        $theme->update($data);

        return redirect()->route('themes.index')->with('success', 'Theme updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param UserTheme $theme
     * @return RedirectResponse
     */
    public function destroy(UserTheme $theme): RedirectResponse
    {
        $theme = UserTheme::findOrFail($theme->id);
        $theme->delete();

        return redirect()->route('themes.index')->with('success', 'Theme deleted successfully.');
    }

    protected function validateRequest()
    {

        return tap(request()->validate([
            'theme_name'        => ['required', 'string', 'max:255'],
            'stylesheet_name'   => ['required', 'string', 'max:255'],
            'user_id'           => ['nullable'],
        ]),function () {

            if(request()->isMethod('POST')) {
                request()->validate([
                    'theme_name'        => ['required', 'string', 'max:255', 'unique:user_themes'],
                ]);
            }

            // Unique value check for update
            if(request()->isMethod('PATCH')) {
                // Finding values
                $theme = request('theme');

                $existingUserTheme = UserTheme::where('theme_name', $theme->theme_name)->firstOrFail();

                // strcmp() is case-sensitive function, this function return 0
                if(strcmp(request()->theme_name, $existingUserTheme['theme_name'])) {
                    request()->validate([
                        'theme_name'        => ['required', 'string', 'max:255', 'unique:user_themes'],
                    ]);
                } else {
                    return false;
                }
            }

        });
    }


    public function toggleTheme(Request $request): JsonResponse
    {
        $user = auth()->user();

        // Toggle between theme IDs based on the user's current theme ID
        $newThemeId = $user->theme_id == 1 ? 2 : 1;

        // Update user's theme preference in the database
        $user->update(['theme_id' => $newThemeId]);

        // You can return a response if needed
        return response()->json(['message' => 'Theme preference toggled successfully']);
    }
}
