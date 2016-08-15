//
//  WzArcSupportInfo.h
//  WzEngine
//
//  Copyright 2010 Winzip. All rights reserved.
//

#import <Cocoa/Cocoa.h>
#import "WzEngineTypes.h"

@protocol WzArcSupportInfo <NSObject>

// various operations for adding files to an archive:
- (BOOL) supportsAddFiles;
- (BOOL) supportsMoveFiles;
- (BOOL) supportsUpdateFiles;
- (BOOL) supportsFreshenFiles;

// WzArcFilespec options for add operations:
- (BOOL) supportsFilespecOptions;

// WzArcAddOptions for add operations:
- (BOOL) supportsAddCompressionMethodOption:(WzCompressionMethodOption)compressionMethodOption;
- (BOOL) supportsAddCompressionLevel:(int) idLevel;
- (BOOL) supportsAddTempPath;
- (BOOL) supportsAddExcludeFilespecs;
- (BOOL) supportsAddSpanning;
- (BOOL) supportsAddIncludeHiddenSystem;
- (BOOL) supportsAddArchiveAttrOnly;
- (BOOL) supportsAddResetArchiveAttr;
- (BOOL) supportsAddStoreExtendedTimes;
- (BOOL) supportsAddStoreUnicodeFilenames;
- (BOOL) supportsAddVolumeLabel;
- (BOOL) supportsAddTimeBefore;
- (BOOL) supportsAddTimeAfter;
- (BOOL) supportsAddEncryptionMethodOption:(WzEncryptionMethodOption) encryptionMethodOption;
- (BOOL) supportsAddEncryptionPassword;
- (BOOL) supportsAddArchiveDate;
- (BOOL) supportsAddSplitOptions;
- (BOOL) supportsAddTouchTime;

// operation for deleting files in an archive:
- (BOOL) supportsDeleteFiles;

// WzArcFilespec options for delete operations:
- (BOOL) supportsDeleteFilespecOptions;

// WzArcDeleteOptions for delete operations:
- (BOOL) supportsDeleteExcludeFilespecs;
- (BOOL) supportsDeleteTempPath;

// operation for renaming files in an archive:
- (BOOL) supportsRenameFiles;

// WzArcRenameOptions for rename operations
- (BOOL) supportsUseUtf8Names;

// operation for extracting files in an archive:
- (BOOL) supportsExtractFiles;

// WzArcExtractOptions for extract operations:
- (BOOL) supportsExtractExcludeFilespec;
- (BOOL) supportsExtractOverwriteNone;
- (BOOL) supportsExtractOverwriteAll;
- (BOOL) supportsExtractCaseInsensitive;
- (BOOL) supportsExtractOnlyNewer;
- (BOOL) supportsExtractConvertSpaces;
- (BOOL) supportsExtractVolumeLabels;
- (BOOL) supportsExtractUpdate;
- (BOOL) supportsExtractProcessAllFiles;
- (BOOL) supportsExtractMatchEntireFilespec;
- (BOOL) supportsExtractUseExtendedTimes;
- (BOOL) supportsExtractInMemoryExtraction;

- (BOOL) supportsExtractPassword;
- (BOOL) supportsExtractTarget;

// operation for splitting an archive:
- (BOOL) supportsSplitArchive;
- (BOOL) supportsSplitArchiveName;
- (BOOL) supportsSplitSplitOption;

// operations for encrypting all files in an archive:
- (BOOL) supportsEncryptArchive;
- (BOOL) supportsEncryptArchiveTempPath;
- (BOOL) supportsEncryptArchivePassword;
- (BOOL) supportsEncryptArchiveMethodOption:(WzEncryptionMethodOption) encryptionMethodOption;

// operations for setting and retrieving archive level comments:
- (BOOL) supportsAddArchiveComment;
- (BOOL) supportsGetArchiveComment;

// operations for setting and retrieving file level comments:
- (BOOL) supportsAddFileComment;
- (BOOL) supportsGetFileComment;

- (BOOL) supportsStoringEmptyFolders;

@end
