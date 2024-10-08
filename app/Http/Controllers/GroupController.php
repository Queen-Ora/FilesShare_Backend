<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateGroupRequest;
use App\Models\Group;
use App\Models\Members;
use App\Models\User;
use Illuminate\Http\Request;

class GroupController extends Controller
{
    public function createGroup(CreateGroupRequest $request){
        $group = new Group;
        $group->name = $request->name;
        $group->description = $request->description;
        $group->admin_id = $request->admin_id;

        //move group avatar to public folder
        if ($request->hasFile('avatar')) {
            $avatar = $request->file('avatar');
            $avatarName = time() . '.' . $avatar->getClientOriginalExtension();
            $avatar->move(public_path('uploads'), $avatarName);
            $group->avatar = $avatarName;
        }
        // Créer le groupe
        $group->save();

        // Ajouter l'admin au groupe 
        // $member = new Members();
        // $member->group_id = $group->id;
        // $member->member_id = $request->admin_id;
        // $member->save();

        return response()->json([
            'message' => 'Group created successfully',
            'group' => $group,
        ], 201);
        
    }
    public function AddMember(Request $request, $groupId)
    {
        try {
            // Valider la requête pour s'assurer que l'utilisateur existe
            $request->validate([
                'user_id' => 'required|exists:users,id', 
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'errors' => $e->validator->errors(),
            ], 422); // Code de statut HTTP pour les erreurs de validation
        }
    
        // Récupérer le groupe ou échouer si introuvable
        $group = Group::findOrFail($groupId);
    
        // Récupérer l'utilisateur à ajouter ou échouer si introuvable
        $user = User::findOrFail($request->user_id);
    
        // Vérifier si l'utilisateur est déjà membre du groupe dans la table 'members'
        $existingMember = Members::where('group_id', $group->id)
                                 ->where('member_id', $user->id)
                                 ->first();
    
        if ($existingMember) {
            return response()->json([
                'message' => 'User is already a member of the group',
            ], 400);
        }
    
        // Ajouter l'utilisateur au groupe (insérer dans la table 'members')
        $member = new Members();
        $member->group_id = $group->id;
        $member->member_id = $user->id;
        $member->save();
    
        return response()->json([
            'message' => 'Member added successfully',
        ], 200);
    }

    public function SelectGroupOfAMember($memberId)
    {
        // Vérifier si l'utilisateur existe
        $member = User::find($memberId);
    
        if (!$member) {
            return response()->json(['message' => 'Member not found'], 404); // 404 Not Found
        }
    
        // Fonction utilitaire pour obtenir l'URL complète de l'avatar
        $getAvatarUrl = function ($avatar) {
            if ($avatar) {
                // Retire le préfixe 'uploads/' s'il existe déjà
                return url('uploads/' . ltrim($avatar, 'uploads/'));
            }
            return null;
        };
    
        // Récupérer les groupes auxquels le membre appartient
        $groups = Members::where('member_id', $memberId)
            ->join('groups', 'groups.id', '=', 'members.group_id')
            ->select('groups.id', 'groups.name', 'groups.avatar')
            ->get()
            ->map(function ($group) use ($getAvatarUrl) {
                $group->avatar = $getAvatarUrl($group->avatar); // Assigne l'URL de l'avatar
                return $group;
            });
    
        // Vérifier si des groupes ont été trouvés
        if ($groups->isEmpty()) {
            return response()->json(['message' => 'No groups found for this member'], 404); // 404 Not Found
        }
    
        return response()->json(['groups' => $groups], 200);
    }
    
//     public function AddMember(Request $request, $groupId){
// //verifier si le groupe existe

// $request->validate([
//     'user_id' => 'required|exists:users,id', // Assure-toi que l'utilisateur existe
// ]);

//   // Récupérer le groupe
//   $group = Group::findOrFail($groupId);

//   // Récupérer l'utilisateur à ajouter
//   $user = User::findOrFail($request->user_id);

//    // Vérifier si l'utilisateur fait déjà partie du groupe
//    if ($group->users()->where('user_id', $user->id)->exists()) {
//     return response()->json([
//         'message' => 'User is already a member of the group',
//     ], 400);
// }

// $member = new Members();
// $member->group_id = $group->id;
// $member->member_id = $user->id;
//   //verifier si cette ligne existe deja dans la base de donnée
//   if ($member->where('group_id', $request->group_id)->where('member_id', $request->user_id)->exists()) {
//     return response()->json(['message' => 'Line already exists in the database'], 400);
// }
// $member->save();

//   return response()->json([
//    'message' => 'Member added successfully',
// ], 200);


//   // Ajouter l'utilisateur au groupe

//         // Vérifier si l'admin est bien l'admin du groupe
//         // Vérifier si l'utilisateur n'est pas déjà dans le groupe
//         // Ajouter l'utilisateur au groupe
//         // $member = new Members();
//         // $member->group_id = $groupId;
//         // $member->member_id = $request->member_id;
//         // $member->save();


//     }

}
