//
//  WzArchive.h
//  WzEngine
//
//  Copyright 2010 Winzip. All rights reserved.
//

#import <Cocoa/Cocoa.h>
#import "WzArcExtractOptions.h"
#import "WzArcAddOptions.h"
#import "WzArcDeleteOptions.h"
#import "WzArcRenameOptions.h"
#import "WzArcSupportInfo.h"

extern NSString * const kWzEngineArchiveDomain;

@protocol WzFileDetails;
@protocol WzArchiveDelegate;
@class WzErrorInfo;
@class WzArcExtractOptions;
@class WzArcEncryptOptions;

@protocol WzArchive <NSObject>

@property (assign)   NSString              *archiveName;
@property (assign)   id<WzArchiveDelegate>  delegate;
@property (readonly) id<WzArcSupportInfo>   supportInfo;

- (BOOL) loaded;
- (BOOL) fullyLoaded;
- (BOOL) empty;
- (WzFileIndex) numberOfFiles;
- (WzFileIndex) numberOfParts;
- (BOOL) alreadyExists;
- (BOOL) readOnly;

- (BOOL) loadWithPassword:(NSString *)password error:(NSError **)error;
- (BOOL) unload;

- (BOOL) encryptedFilesAreLoaded;
- (WzFileIndex) nameToIndex:(NSString *)name error:(NSError **)error;
- (id<WzFileDetails>) fileInArchive:(WzFileIndex)index error:(NSError **)error;

- (BOOL) addFiles:(WzArcAddOptions *)addOptions error:(NSError **)error;
- (BOOL) moveFiles:(WzArcAddOptions *)addOptions error:(NSError **)error;
- (BOOL) updateFiles:(WzArcAddOptions *)addOptions error:(NSError **)error;
- (BOOL) freshenFiles:(WzArcAddOptions *)addOptions error:(NSError **)error;
- (BOOL) deleteFiles:(WzArcDeleteOptions *)deleteOptions error:(NSError **)error;
- (BOOL) renameFiles:(WzArcRenameOptions *) renameOptions error:(NSError **)error;
- (BOOL) extractFilesWithOptions:(WzArcExtractOptions *)options error:(NSError **)error;
- (BOOL) encryptArchive:(WzArcEncryptOptions *)encryptOptions error:(NSError **)error;
- (void) abort;

@end


@protocol WzArchiveDelegate <NSObject>

@optional
- (WzMsgBoxResponse) wzArchive:(id<WzArchive>)archive msgBox:(NSString *)text withCaption:(NSString *)caption options:(WzMsgBoxOption)options;
- (BOOL) wzArchive:(id<WzArchive>)archive responseForMessage:(NSString *)msg withErrorLevel:(NSInteger)level andMsgID:(NSInteger)msgID;

- (WzDiskChangeResponse) wzArchive:(id<WzArchive>)archive promptsForDiskId:(NSInteger)disk withMessage:(NSString *)msg;
- (WzDiskChangeResponse) wzArchive:(id<WzArchive>)archive promptsForDiskOneWithMessage:(NSString *)msg;

- (void) wzArchiveLoadingStarted:(id<WzArchive>)archive;
- (void) wzArchiveLoadingFinished:(id<WzArchive>)archive;
- (void) wzArchiveOperationStarted:(id<WzArchive>)archive;
- (void) wzArchive:(id<WzArchive>)archive operationPercentComplete:(NSInteger)percentageComplete;
- (void) wzArchiveOperationFinished:(id<WzArchive>)archive;
- (void) wzArchive:(id<WzArchive>)archive statusText:(NSString*)text option:(WzStatusWindowOption)option;

// callbacks used by add operations (must provide basic guarantee)
// TODO: - (BOOL) AddNewLocationPrompt(int idPart, const WzLib::FidString& fstrCurrentPath, int cbIncrement, bool fRepeat, WzLib::FidString& fstrNewPath);
// TODO: - (BOOL) DualMeterCommand(const WzDualMeterCommand wdmc, UINT32 ui32Data);
// TODO: - (WzDenyWriteResponse) DenyWrite(const WzLib::FidString& fstrFileid);
// TODO: - (void) MeterSwap(const WzMeterOption wmo);

// callbacks used by extract/test operations (must provide basic guarantee)
// TODO: - (BOOL) CFlagOutput(const char *pszOutput, int cbOutput);
// TODO: - WzYesNoAllCancelResponse AttributePrompt(const WzLib::FidString& fstrFileid);
// TODO: - WzYesNoAllCancelResponse DotDotPrompt(const WzLib::FidString& fstrTarget);
- (WzYesNoAllCancelResponse) attributePromptForFileName:(NSString *)fileName;
- (WzYesNoAllNoneRenameCancelResponse) replacePromptForFileName:(NSString *)fileName;
- (WzIncorrectPasswordResponse) password:(NSString **)password forFilename:(NSString *)filename promptOption:(WzPasswordPromptOption) promptOption;

- (NSString *) wzArchive:(id<WzArchive>)archive extractLocationOfPartId:(NSInteger)part files:(NSArray *)files;

@end

