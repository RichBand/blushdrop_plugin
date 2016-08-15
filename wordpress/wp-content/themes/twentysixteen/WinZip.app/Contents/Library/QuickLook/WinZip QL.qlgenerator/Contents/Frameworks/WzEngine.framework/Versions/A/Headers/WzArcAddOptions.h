//
//  WzArcAddOptions.h
//  WzEngine
//
//  Copyright 2010 Winzip. All rights reserved.
//

#import <Cocoa/Cocoa.h>
#import "WzDynamicContainer.h"
#import "WzEngineTypes.h"

@protocol WzArchive;

@interface WzArcAddOptions : WzDynamicContainer {
	NSMutableArray *includeFiles;
	NSMutableArray *excludeFiles;
}

@property (assign) WzCompressionMethodOption compressionMethod;
@property (assign) int compressionLevel;
@property (assign) BOOL useDosNames;
@property (assign) BOOL spanning;
@property (assign) BOOL includeHiddenSystem;
@property (assign) BOOL clearHRSAttr;
@property (assign) BOOL archiveAttrOnly;
@property (assign) BOOL moveFilesToRecycleBin;
@property (assign) BOOL resetArchiveAttr;
@property (assign) BOOL storeExtendedTimes;
@property (assign) BOOL storeUnicodeFilenames;
@property (assign) NSDate *timeBefore;
@property (assign) NSDate *timeAfter;
@property (assign) WzEncryptionMethodOption encryptionMethodOption;
@property (assign) NSString *encryptionPassword;
@property (assign) NSDate *touchTime;


// operations
- (void)includeFile:(NSString *)fullpath intoFolder:(NSString *)basepath recurse:(BOOL)recurse;
- (void)excludeFile:(NSString *)fullpath recurse:(BOOL)recurse;

@end
