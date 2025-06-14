<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;

class User extends Authenticatable
{
    use HasApiTokens;

    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory;
    use HasProfilePhoto;
    use Notifiable;
    use TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    
    protected $fillable = [
        'name',
        'email',
        'first_name',
        'last_name',
        'gender',
        'city',
        'country',
        'is_verified',
        'middle_initial',
        'password',
        'last_seen_at',
    ];

    protected $casts = [
        'last_seen_at' => 'datetime',
        'opt_out_feedback' => 'boolean',
        'last_feedback_at' => 'datetime',
    ];
    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'profile_photo_url', 'full_name', 'encrypted_id',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
    public function educationalBackgrounds() {
        return $this->hasMany(EducationalBackground::class);
    }

    public function professionalExams() {
        return $this->hasMany(ProfessionalExam::class);
    }

    public function trainingAttendeds()
    {
        return $this->hasMany(TrainingAttended::class);
    }
    public function competencies()
{
    return $this->hasMany(Competency::class);
}

    public function employmentHistory() {
        return $this->hasMany(EmploymentHistory::class);
    }

    public function alumniLocation() {
        return $this->hasOne(AlumniLocation::class);
    }
    public function employmentHistories()
    {
        return $this->hasMany(EmploymentHistory::class);
    }
    public function employmentStatus()
    {
        return $this->hasMany(EmploymentStatus::class);
    }
    public function jobPostings()
    {
        return $this->hasMany(JobPosting::class);
    }
    public function posts()
    {
        return $this->hasMany(Post::class);
    }
    public function events()
    {
        return $this->hasMany(Event::class);
    }
    
    public function comments()
    {
        return $this->hasMany(Comment::class, 'user_id');
    }
      // Define relationships
  
      public function lastMessage()
      {
          return $this->belongsTo(Chat::class, 'last_message_id');
      }
      public function conversations()
      {
          return $this->belongsToMany(
              Conversation::class,
              'conversation_participants',
              'user_id',
              'conversation_id'
          )->withTimestamps();
      }
  
      /**
       * Get all messages sent by the user.
       */
      public function sentMessages()
      {
          return $this->hasMany(Message::class, 'sender_id');
      }
  
      /**
       * Get all messages received by the user in personal chats.
       */
      public function receivedMessages()
      {
          return $this->hasManyThrough(
              Message::class,
              Conversation::class,
              'id', // Foreign key on conversations table
              'conversation_id' // Foreign key on messages table
          )->where('type', 'personal');
      }
  
      /**
       * Get all attachments uploaded by the user.
       */
      public function attachments()
      {
          return $this->hasManyThrough(
              Attachment::class,
              Message::class,
              'sender_id', // Foreign key on messages table
              'message_id' // Foreign key on attachments table
          );
      }
  
      /**
       * Get the educational background of the user.
       */
      public function educationalBackground()
      {
          return $this->hasOne(EducationalBackground::class);
      }
      public function following()
      {
          return $this->hasMany(Follow::class, 'follower_id');
      }
  
      // Relationship: Get all users who are following this user
      public function followers()
      {
          return $this->hasMany(Follow::class, 'followed_id');
      }
  
      // Helper Method: Check if the current user is following another user
      public function isFollowing($userId)
      {
          return $this->following()->where('followed_id', $userId)->exists();
      }
  
      // Helper Method: Get the list of users this user is following
      public function getFollowingUsers()
      {
          return $this->following()->with('followed')->get()->pluck('followed');
      }
  
      // Helper Method: Get the list of users who are following this user
      public function getFollowersUsers()
      {
          return $this->followers()->with('follower')->get()->pluck('follower');
      }
      public function isOnline()
      {
          return $this->last_seen_at && $this->last_seen_at->gt(now()->subMinutes(2));
      }
      public function getFullNameAttribute()
{
    return $this->first_name . ' ' . $this->last_name;
}
public function getEncryptedIdAttribute()
{
    return Crypt::encryptString($this->id);
}
public function updateCoverPhoto($photo)
{
    tap($this->cover_photo_path, function ($previous) use ($photo) {
        $this->forceFill([
            'cover_photo' => $photo->storePublicly(
                'cover-photos', ['disk' => $this->profilePhotoDisk()]
            ),
        ])->save();

        if ($previous) {
            Storage::disk($this->profilePhotoDisk())->delete($previous);
        }
    });
}

public function getCoverPhotoUrlAttribute()
{
    return $this->cover_photo_path
        ? Storage::disk($this->profilePhotoDisk())->url($this->cover_photo_path)
        : null;
}
public function contentItems()
{
    return $this->hasMany(ContentItem::class);
}
public function employabilityIndex()
{
    return $this->hasOne(EmployabilityIndex::class);
}

public function trainingsAttended()
{
    return $this->hasMany(TrainingAttended::class, 'user_id');
}
public function location()
{
    return $this->hasOne(AlumniLocation::class);
}
public function jobHuntingExperiences()
{
    return $this->hasMany(JobHuntingExperience::class);
}
public function unemployedDetails()
{
    return $this->hasOne(UnemployedDetail::class);
}
}
