<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateGroupRequest;
use App\Models\Group;
use App\Models\GroupMessage;
use App\Models\Invitation;
use App\Models\Members;
use App\Models\User;
use Illuminate\Http\Request;

class GroupController extends Controller
{
    public function createGroup(CreateGroupRequest $request)
    {
        $group = new Group;
        $group->name = $request->name;
        $group->description = $request->description;
        $group->admin_id = $request->admin_id;
        $group->avatar = "default.png";

        //move group avatar to public folder
        // if ($request->hasFile('avatar')) {
        //     $avatar = $request->file('avatar');
        //     $avatarName = time() . '.' . $avatar->getClientOriginalExtension();
        //     $avatar->move(public_path('uploads'), $avatarName);
        //     $group->avatar = $avatarName;
        // }
        // Créer le groupe
        $group->save();

        // Ajouter l'admin au groupe 
        $member = new Members();
        $member->group_id = $group->id;
        $member->member_id = $request->admin_id;
        $member->save();

        return response()->json([
            'message' => 'Group created successfully',
            'group' => $group,
        ], 201);
    }
    public function AddMember(Request $request, $groupId)
    {
        try {
            // Validation de l'email requis
            $request->validate([
                'email' => 'required|string|email',
            ]);
    
            // Recherche de l'utilisateur avec l'email
            $memberSearch = User::where('email', $request->email)->first();
    
            // Si l'utilisateur n'est pas trouvé, envoyer une invitation
            if (!$memberSearch) {
                $invite = new Invitation();
                $invite->group_id = $groupId;
                $invite->email = $request->email;
                $invite->save();
    
                return response()->json([
                    'message' => 'Invitation envoyée avec succès',
                ], 200);
            }
    
            // Vérification si le membre existe déjà dans ce groupe
            if (Members::where('group_id', $groupId)->where('member_id', $memberSearch->id)->exists()) {
                return response()->json(['message' => 'Le membre fait déjà partie du groupe'], 400);
            }
    
            // Ajout du membre dans le groupe
            $member = new Members();
            $member->group_id = $groupId;
            $member->member_id = $memberSearch->id;
            $member->save();
    
            return response()->json([
                'message' => 'Membre ajouté avec succès',
            ], 201); // 201 : Création réussie
    
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'errors' => $e->validator->errors(),
            ], 422); // Erreurs de validation
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erreur interne du serveur',
            ], 500); // Erreur interne pour les autres exceptions
        }
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

    public function SendFile(Request $request,  $id,$groupId){
        try{
            $request->validate([
                'file' => 'nullable|file|max:10240', // 10MB maximum
            ]);
    $message = new GroupMessage();
    $message->group_id = $groupId;
    $message->sender_id = $id;

    if ($request->hasFile('file')) {
        $file = $request->file('file');
        $originalName = $file->getClientOriginalName();
        // $fileName = time() . '.' . $file->getClientOriginalExtension();
        $file->move(public_path('uploads'), $originalName);
        $message->file = $originalName;
    }

    $message->save();

    return response()->json([
        'message' => 'File sent successfully',
        'messages' => $message,
        'originalName' => $originalName
    ], 201);
          
}

catch(\Exception $e){
    return response()->json([
        'error' => 'Erreur interne du serveur',
    ], 500); // Erreur interne pour les autres exceptions
}catch(\Illuminate\Validation\ValidationException $e){
    return response()->json([
        'errors' => $e->validator->errors(),
    ], 422); // Erreurs de validation
}
    

    }

    public function displayFile( $groupId){
        $messages = GroupMessage::where('group_id', $groupId)->get();
        $fileNames = [];
        foreach ($messages as $message) {
            if($message->file){
                $fileNames[] = $message->file;
            }
        }

        return response()->json([
            'files' => $fileNames,
        ], 200);
    }


 


}
