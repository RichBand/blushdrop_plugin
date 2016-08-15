//
//  WzArcExtractOptions.h
//  WzEngine
//
//  Copyright 2010 Winzip. All rights reserved.
//

#import <Cocoa/Cocoa.h>
#import "WzDynamicContainer.h"

@protocol WzArchive;

@interface WzArcExtractOptions : WzDynamicContainer {
	NSMutableArray *includeFiles;
	NSMutableArray *excludeFiles;
}

@property (assign) BOOL lowercaseNames;
@property (assign) BOOL noPath;
@property (assign) BOOL overwriteNone;
@property (assign) BOOL overwriteAll;
@property (assign) BOOL caseInsensitive;
@property (assign) BOOL onlyNewer;
@property (assign) BOOL convertSpaces;
@property (assign) BOOL volumeLabels;
@property (assign) BOOL update;
@property (assign) BOOL processAllFiles;
@property (assign) BOOL restoreHRSAttr;
@property (assign) BOOL cFlag;
@property (assign) BOOL matchEntireFilespec;
@property (assign) BOOL useExtendedTimes;
@property (assign) BOOL inMemoryExtraction;
@property (assign) BOOL translate;
@property (assign) BOOL crLfConversion;
@property (assign) NSString *encryptionPassword;
@property (assign) NSString *extractTo;

// operations
- (void)includeFile:(NSString *)path;
- (void)includeFile:(NSString *)path strippingLeadingByteCount:(NSInteger)bytesToStrip;
- (void)excludeFile:(NSString *)path;

@end