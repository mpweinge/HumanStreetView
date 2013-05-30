//
//  StatViewController.m
//  HumanStreetViewiOS
//
//  Created by Michael Weingert on 2013-05-27.
//  Copyright (c) 2013 Michael Weingert. All rights reserved.
//

#import "StatViewController.h"
#import "CameraViewController.h"

@interface StatViewController ()

@end

@implementation StatViewController

- (void)viewDidLoad
{
    [super viewDidLoad];
	// Do any additional setup after loading the view, typically from a nib.
    NSString * fullURL = @"http://99.249.128.170/HumanStreetView/TrailsIndex.html";
    NSURL * url = [NSURL URLWithString:fullURL];
    NSURLRequest * requestObj = [NSURLRequest requestWithURL:url];

    [_webView loadRequest:requestObj];
    _webView.delegate = self;
    
    _isTakingPhoto = 0;
}

-(void)viewDidAppear:(BOOL)animated
{
    if (_isTakingPhoto == 1)
    {
        NSString* callback = @"NativeBridge.resultForCallback(";
        callback = [callback stringByAppendingString:[@(_callbackID) description]];
        callback = [callback stringByAppendingString:@", '');"];
        [self ExecJavascriptFunction:callback];
    }
}

- (void)didReceiveMemoryWarning
{
    [super didReceiveMemoryWarning];
    // Dispose of any resources that can be recreated.
}

- (void) ExecJavascriptFunction : (NSString*) javascriptString
{
    [_webView stringByEvaluatingJavaScriptFromString:javascriptString];
}

// This function is called on all location change :
- (BOOL)webView:(UIWebView *)webView2
shouldStartLoadWithRequest:(NSURLRequest *)request
 navigationType:(UIWebViewNavigationType)navigationType
{
    
    NSString *requestString = [[request URL] absoluteString];
    
    //NSLog(@"request : %@",requestString);
    
    if ([requestString hasPrefix:@"js-frame:"]) {
        
        NSArray *components = [requestString componentsSeparatedByString:@":"];
        
        NSString *function = (NSString*)[components objectAtIndex:1];
		int callbackId = [((NSString*)[components objectAtIndex:2]) intValue];
        NSString *argsAsString = [(NSString*)[components objectAtIndex:3]
                                  stringByReplacingPercentEscapesUsingEncoding:NSUTF8StringEncoding];
        
        NSError *e = nil;
        id jsonObject = [NSJSONSerialization JSONObjectWithData: [argsAsString dataUsingEncoding:NSUTF8StringEncoding] options: NSJSONReadingMutableContainers error: &e];
        
        if ([jsonObject isKindOfClass:[NSArray class]]) {
            NSLog(@"its an array!");
            NSArray *jsonArray = (NSArray *)jsonObject;
            NSLog(@"jsonArray - %@",jsonArray);
        }
        else {
            NSLog(@"its probably a dictionary");
            NSDictionary *jsonDictionary = (NSDictionary *)jsonObject;
            NSLog(@"jsonDictionary - %@",jsonDictionary);
        }
        
        NSArray* jsonArray = (NSArray*)jsonObject;
        _callbackID = callbackId;
        [self handleCall:function callbackId:callbackId args:jsonArray];
        
        return NO;
    }
    
    // Accept this location change
    return YES;
    
}

// Implements all you native function in this one, by matching 'functionName' and parsing 'args'
// Use 'callbackId' with 'returnResult' selector when you get some results to send back to javascript
- (void)handleCall:(NSString*)functionName callbackId:(int)callbackId args:(NSArray*)args
{
    //eventually maintain a dictionary of functions here
    _isTakingPhoto = 1;
    [self TakePhoto];
}

- (void) TakePhoto
{
    CameraViewController* newCamView = [[CameraViewController alloc] init];
    [self.navigationController pushViewController:newCamView animated:YES];
}

- (void)myObjectiveCFunction {
    
    // Do whatever you want!
    
}

@end
