//
//  CloudService.h
//  CloudServiceSDK
//
//  Created by User on 7/18/13.
//  Copyright (c) 2013 User. All rights reserved.
//

#import <Foundation/Foundation.h>

@protocol CloudEntry;
@protocol CloudFolder;
@protocol CloudFile;
@protocol CloudRequest;

typedef void (^CloudCommonCompletionBlock)(NSError *error);
typedef void (^CloudInProgressBlock)(CGFloat progress);

typedef void (^CloudEntryRenameCompletionBlock)(id<CloudEntry> newEntry, NSError *error);
typedef void (^CloudEntryMoveCompletionBlock)(id<CloudEntry> newEntry, NSError *error);
typedef void (^CloudEntryCopyCompletionBlock)(id<CloudEntry> newEntry, NSError *error);
typedef void (^CloudEntryLoadSharableLinkCompletionBlock)(NSString *link, NSError *error);

typedef enum {
    EntryCapabilityDelete = 1 << 0,
    EntryCapabilityRename = 1 << 1,
    EntryCapabilityNewFolder = 1 << 2
} EntryCapability;
typedef NSUInteger EntryCapabilitySet;

@protocol CloudEntry <NSObject>

- (NSString *)entryId;
- (BOOL)isLoaded;
- (NSString *)name;
- (id<CloudFolder>)parent;
- (NSDate *)createdDate;
- (NSDate *)modifiedDate;
- (EntryCapabilitySet)capabilities;

- (void)loadMetadataWithCompletionBlock:(CloudCommonCompletionBlock)completionBlock;
- (void)renameToName:(NSString *)name completionBlock:(CloudEntryRenameCompletionBlock)completionBlock;
- (void)moveToFolder:(id<CloudFolder>)folder completionBlock:(CloudEntryMoveCompletionBlock)completionBlock;
- (void)copyToFolder:(id<CloudFolder>)folder completionBlock:(CloudEntryCopyCompletionBlock)completionBlock;
- (void)deleteWithCompletionBlock:(CloudCommonCompletionBlock)completionBlock;
- (void)loadSharableLinkWithCompletionBlock:(CloudEntryLoadSharableLinkCompletionBlock)completionBlock;
@end

typedef void (^CloudFolderListCompletionBlock)(NSArray *results, NSError *error);
typedef void (^CloudFolderCreateFolderCompletionBlock)(id<CloudFolder> newFolder, NSError *error);
typedef void (^CloudFolderUploadCompletionBlock)(id<CloudFile> uploadedFile, NSError *error);

@protocol CloudFolder <CloudEntry>

- (void)listWithCompletionBlock:(CloudFolderListCompletionBlock)completionBlock;
- (void)createFolder:(NSString *)name completionBlock:(CloudFolderCreateFolderCompletionBlock)completionBlock;
- (id<CloudRequest>)uploadFile:(NSString *)name
          fromPath:(NSString *)sourcePath
   completionBlock:(CloudFolderUploadCompletionBlock)completionBlock
   inProgressBlock:(CloudInProgressBlock)inProgressBlock;
// find receiver's immediate child entries that match name.
- (void)findForName:(NSString *)name completionBlock:(CloudFolderListCompletionBlock)completionBlock;

@optional

// search receiver's child entries recursively whose name contains the given keyword as a substring.
- (void)searchForKeyword:(NSString *)keyword completionBlock:(CloudFolderListCompletionBlock)completionBlock;

@end

@protocol CloudFile <CloudEntry>

- (NSNumber *)fileSize;

- (id<CloudRequest>)downloadFileIntoPath:(NSString *)destPath
                         completionBlock:(CloudCommonCompletionBlock)completionBlock
                         inProgressBlock:(CloudInProgressBlock)inProgressBlock;

@end

typedef enum {
    CloudCapabilityFindFile = 1 << 0,
    CloudCapabilityDuplicateFile = 1 << 1,
    CloudCapabilityIgnoreCase = 1 << 2
} CloudCapability;
typedef NSUInteger CloudCapabilitySet;

typedef enum {
    KnownFolderKeyRoot = 1 << 0,
    KnownFolderKeyShare = 1 << 1,
    KnownFolderKeyTrash = 1 << 2,
    KnownFolderKeyMyDocuments = 1 << 3,
    KnownFolderKeyMyPhotos = 1 << 4,
    KnownFolderKeyPublicDocuments = 1 << 5,
    KnownFolderKeyCameraRoll = 1 << 6,
    KnownFolderKeyMagicBriefcase = 1 << 7,
    KnownFolderKeyMobilePhotos = 1 << 8,
    KnownFolderKeyWebArchive = 1 << 9,
    KnownFolderKeyComputer = 1 << 10,
    KnownFolderKeyLibraries = 1 << 11,
    KnownFolderKeyNetwork = 1 << 12,
} KnownFolderKey;
typedef NSUInteger KnownFolderKeySet;

@class CloudAccountInfo;
@class CloudQuota;
@protocol CloudService;

typedef void (^CloudServiceLoadKnownFolderCompletionBlock)(id<CloudFolder> knownFolder, NSError *error);
typedef void (^CloudServiceLoadAccountInfoCompletionBlock)(CloudAccountInfo *accountInfo, NSError *error);
typedef void (^CloudServiceLoadQuotaCompletionBlock)(CloudQuota *quota, NSError *error);

//zipshare login view need show in current window on winzip MAC, need app to call [NSView tag] to set the correct view a tag to draw login view.
static const NSInteger kZipShareContextViewTag = 1000;

@protocol CloudService <NSObject>

- (CloudCapabilitySet)capabilities;
- (KnownFolderKeySet)supportedFolders;
- (id<CloudFile>)fileWithId:(NSString *)entryId name:(NSString *)name;
- (id<CloudFolder>)folderWithId:(NSString *)entryId name:(NSString *)name;
- (NSNumber *)maxUploadSize;

- (BOOL)isLogin;
- (void)loginFromContext:(NSObject *)context completionBlock:(CloudCommonCompletionBlock)completionBlock;
- (void)logout;

- (void)loadKnownFolderWithKey:(KnownFolderKey)key completionBlock:(CloudServiceLoadKnownFolderCompletionBlock)completionBlock;
- (void)loadAccountInfoWithCompletionBlock:(CloudServiceLoadAccountInfoCompletionBlock)completionBlock;
- (void)loadQuotaWithCompletionBlock:(CloudServiceLoadQuotaCompletionBlock)completionBlock;

@optional

- (BOOL)handleOpenURL:(NSURL *)url;

@end

@protocol CloudRequest <NSObject>

- (void)cancelRequests;

@end
